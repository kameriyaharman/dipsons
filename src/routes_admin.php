<?php
// Admin panel for owner & staff
$L = 'admin/layout';

function admin(): ?array {
    static $a = false;
    if ($a === false) $a = !empty($_SESSION['admin_id']) ? row('SELECT * FROM admins WHERE id=? AND active=1', [$_SESSION['admin_id']]) : null;
    return $a;
}
function admin_name(): string { return admin()['name'] ?? ''; }
function is_owner(): bool { return (admin()['role'] ?? '') === 'owner'; }
function staff_names(): array { return array_column(rows('SELECT name FROM admins WHERE active=1 ORDER BY name'), 'name'); }

// ---- First-time setup: create owner account if none exists
if ($path === '/admin/setup') {
    if (val('SELECT COUNT(*) FROM admins')) redirect('/admin/login');
    $err = '';
    if (is_post()) {
        check_csrf();
        if (strlen(post('password')) < 8) $err = 'Password must be at least 8 characters.';
        elseif (!preg_match('/^[a-z0-9_.]{3,40}$/i', post('username'))) $err = 'Username: 3–40 letters/numbers.';
        else {
            q('INSERT INTO admins (name,username,password_hash,role) VALUES (?,?,?,?)', [post('name') ?: 'Owner', strtolower(post('username')), password_hash(post('password'), PASSWORD_DEFAULT), 'owner']);
            $_SESSION['admin_id'] = (int)db()->lastInsertId();
            redirect('/admin');
        }
    }
    render('admin/login', ['title' => 'Create owner account', 'err' => $err, 'setup' => true]);
}

if ($path === '/admin/login') {
    if (!val('SELECT COUNT(*) FROM admins')) redirect('/admin/setup');
    $err = '';
    if (is_post()) {
        check_csrf();
        $tries = $_SESSION['login_tries'] ?? [0, 0];
        if ($tries[0] >= 5 && time() - $tries[1] < 300) $err = 'Too many attempts. Please wait 5 minutes.';
        else {
            $a = row('SELECT * FROM admins WHERE username=? AND active=1', [strtolower(post('username'))]);
            if ($a && password_verify(post('password'), $a['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$a['id'];
                unset($_SESSION['login_tries']);
                redirect($_SESSION['admin_next'] ?? '/admin');
            }
            $_SESSION['login_tries'] = [$tries[0] + 1, time()];
            $err = 'Wrong username or password.';
        }
    }
    render('admin/login', ['title' => 'Admin login', 'err' => $err, 'setup' => false]);
}

if ($path === '/admin/logout') { unset($_SESSION['admin_id']); redirect('/admin/login'); }

if (!admin()) { $_SESSION['admin_next'] = $_SERVER['REQUEST_URI']; redirect('/admin/login'); }
header('X-Robots-Tag: noindex');

$newCount = (int)val("SELECT COUNT(*) FROM enquiries WHERE status='new'");
$base = ['newCount' => $newCount];

// ---- Helpers for lists
function enquiry_filters(): array {
    $w = ['1=1']; $a = [];
    $st = get('status'); if ($st && isset(STATUSES[$st])) { $w[] = 'e.status=?'; $a[] = $st; }
    $sv = (int)get('service'); if ($sv) { $w[] = 'EXISTS (SELECT 1 FROM enquiry_services es WHERE es.enquiry_id=e.id AND es.service_id=?)'; $a[] = $sv; }
    $src = get('source'); if ($src && isset(SOURCES[$src])) { $w[] = 'e.source=?'; $a[] = $src; }
    $qq = get('q');
    if ($qq !== '') {
        $like = '%' . $qq . '%'; $digits = preg_replace('/\D/', '', $qq); $veh = '%' . clean_vehicle($qq) . '%';
        $w[] = '(e.name LIKE ? OR e.code LIKE ? OR REPLACE(e.vehicle_number," ","") LIKE ?' . ($digits !== '' ? ' OR e.mobile LIKE ?' : '') . ')';
        array_push($a, $like, $like, $veh); if ($digits !== '') $a[] = '%' . $digits . '%';
    }
    $from = get('from'); if ($from) { $w[] = 'e.created_at >= ?'; $a[] = $from . ' 00:00:00'; }
    $to = get('to'); if ($to) { $w[] = 'e.created_at <= ?'; $a[] = $to . ' 23:59:59'; }
    return [implode(' AND ', $w), $a];
}

function renewals_due(int $days, int $limit = 200): array {
    // every expiry date (insurance, puc, tax, fitness, permit) due within $days, or expired in the last 30 days
    $parts = [];
    foreach (['insurance' => 'insurance_expiry', 'puc' => 'puc_expiry', 'tax' => 'tax_expiry', 'fitness' => 'fitness_expiry', 'permit' => 'permit_expiry'] as $k => $col) {
        $parts[] = "SELECT v.id vid, v.number, v.type, c.id cid, c.name, c.mobile, c.notify_whatsapp, '$k' kind, v.$col due
                    FROM vehicles v JOIN customers c ON c.id=v.customer_id
                    WHERE v.$col IS NOT NULL AND v.$col BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL $days DAY";
    }
    $sql = 'SELECT r.*, (SELECT MAX(sent_at) FROM reminder_log l WHERE l.vehicle_id=r.vid AND l.kind=r.kind AND l.due_date=r.due) last_sent
            FROM (' . implode(' UNION ALL ', $parts) . ') r ORDER BY r.due LIMIT ' . $limit;
    return rows($sql);
}
function reminder_text(array $r): string {
    return fill_template(setting('reminder_template'), [
        'name' => $r['name'] ?: 'Customer', 'kind' => EXPIRY_KINDS[$r['kind']] ?? $r['kind'], 'vehicle' => fmt_vehicle($r['number']),
        'date' => fmt_date($r['due']), 'phone' => setting('phone1'),
    ]);
}

// ================= Dashboard
if ($path === '/admin') {
    $stats = [
        'today' => (int)val('SELECT COUNT(*) FROM enquiries WHERE DATE(created_at)=CURDATE()'),
        'today_src' => rows('SELECT source, COUNT(*) n FROM enquiries WHERE DATE(created_at)=CURDATE() GROUP BY source'),
        'pending' => (int)val("SELECT COUNT(*) FROM enquiries WHERE status IN ('new','called','docs_pending')"),
        'callback_due' => (int)val("SELECT COUNT(*) FROM enquiries WHERE callback_at IS NOT NULL AND callback_at <= NOW() AND status NOT IN ('done','cancelled')"),
        'done_month' => (int)val("SELECT COUNT(*) FROM enquiries WHERE status='done' AND updated_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')"),
        'renewals7' => count(array_filter(renewals_due(7), fn($r) => $r['due'] >= date('Y-m-d'))),
    ];
    $bySvc = rows("SELECT s.name, COUNT(*) n FROM enquiry_services es JOIN services s ON s.id=es.service_id JOIN enquiries e ON e.id=es.enquiry_id
                   WHERE e.created_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01') GROUP BY s.id ORDER BY n DESC LIMIT 6");
    $recent = rows('SELECT * FROM enquiries e ORDER BY id DESC LIMIT 8');
    $callbacks = rows("SELECT * FROM enquiries WHERE callback_at IS NOT NULL AND status NOT IN ('done','cancelled') ORDER BY callback_at LIMIT 6");
    $ren = array_slice(renewals_due(7), 0, 8);
    render('admin/dashboard', $base + ['title' => 'Dashboard', 'nav' => 'dash', 'stats' => $stats, 'bySvc' => $bySvc, 'recent' => $recent, 'ren' => $ren, 'callbacks' => $callbacks], $L);
}

// ================= Enquiries list
if ($path === '/admin/enquiries') {
    [$w, $a] = enquiry_filters();
    $page = max(1, (int)get('p', 1)); $per = 30;
    $total = (int)val("SELECT COUNT(*) FROM enquiries e WHERE $w", $a);
    $list = rows("SELECT e.* FROM enquiries e WHERE $w ORDER BY e.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $a);
    $counts = []; foreach (rows('SELECT status, COUNT(*) n FROM enquiries GROUP BY status') as $r) $counts[$r['status']] = (int)$r['n'];
    render('admin/enquiries', $base + ['title' => 'Enquiries', 'nav' => 'enq', 'list' => $list, 'total' => $total, 'page' => $page, 'per' => $per, 'counts' => $counts], $L);
}

if ($path === '/admin/enquiries/export') {
    [$w, $a] = enquiry_filters();
    $list = rows("SELECT e.* FROM enquiries e WHERE $w ORDER BY e.id DESC", $a);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="dipsons-enquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Enquiry', 'Date', 'Customer', 'Mobile', 'Services', 'Vehicle type', 'Vehicle no.', 'Insurance expiry', 'PUC expiry', 'Source', 'Status', 'Assigned', 'Charges', 'Payment', 'Message']);
    foreach ($list as $r) fputcsv($out, [$r['code'], $r['created_at'], $r['name'], $r['mobile'], $r['service_names'], $r['vehicle_type'], $r['vehicle_number'], $r['insurance_expiry'], $r['puc_expiry'], SOURCES[$r['source']] ?? $r['source'], STATUSES[$r['status']] ?? $r['status'], $r['assigned_to'], $r['charges'], $r['payment_status'], $r['message']]);
    exit;
}

if ($path === '/admin/enquiries/new') {
    if (is_post()) {
        check_csrf();
        $m = clean_mobile(post('mobile'));
        $services = array_filter(array_map('intval', (array)($_POST['services'] ?? [])));
        if (!valid_mobile($m) || post('name') === '' || !$services) {
            flash('Name, valid mobile and at least one service are required.', 'err');
            $_SESSION['old'] = $_POST; redirect('/admin/enquiries/new');
        }
        $files = []; foreach (files_list('docs') as $i => $f) $files['Document ' . ($i + 1)] = $f;
        $r = create_enquiry([
            'name' => post('name'), 'mobile' => $m, 'services' => $services, 'vehicle_type' => post('vehicle_type'),
            'vehicle_number' => post('vehicle_number'), 'vehicle_model' => post('vehicle_model'),
            'insurance_expiry' => post('insurance_expiry'), 'puc_expiry' => post('puc_expiry'),
            'message' => post('message'), 'source' => post('source', 'call'), 'remind' => 1, 'by' => admin_name(),
        ], $files);
        if (post('assigned_to')) q('UPDATE enquiries SET assigned_to=? WHERE id=?', [post('assigned_to'), $r['id']]);
        flash('Enquiry ' . $r['code'] . ' added.');
        redirect('/admin/enquiries/' . $r['code']);
    }
    $cust = get('customer') ? row('SELECT * FROM customers WHERE id=?', [(int)get('customer')]) : null;
    render('admin/enquiry_new', $base + ['title' => 'Add enquiry', 'nav' => 'enq', 'cust' => $cust], $L);
}

if (preg_match('#^/admin/enquiries/(DPS\d+)(/zip)?$#', $path, $m)) {
    $enq = row('SELECT * FROM enquiries WHERE code=?', [$m[1]]);
    if (!$enq) not_found();
    $eid = (int)$enq['id'];

    if (!empty($m[2])) { // download all documents as zip
        $docs = rows('SELECT * FROM documents WHERE enquiry_id=?', [$eid]);
        if (!$docs || !class_exists('ZipArchive')) { flash('No documents to download.', 'err'); redirect('/admin/enquiries/' . $enq['code']); }
        $tmp = tempnam(sys_get_temp_dir(), 'dz');
        $z = new ZipArchive(); $z->open($tmp, ZipArchive::OVERWRITE);
        foreach ($docs as $i => $d) {
            $f = UPLOAD_DIR . '/' . $d['filename'];
            if (is_file($f)) $z->addFile($f, ($i + 1) . '-' . preg_replace('/[^A-Za-z0-9 _.-]/', '', $d['label']) . '.' . pathinfo($f, PATHINFO_EXTENSION));
        }
        $z->close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $enq['code'] . '-documents.zip"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp); unlink($tmp); exit;
    }

    if (is_post()) {
        check_csrf();
        $act = post('action');
        $back = '/admin/enquiries/' . $enq['code'];
        if ($act === 'update') {
            $st = post('status');
            if (isset(STATUSES[$st]) && $st !== $enq['status']) {
                $public = ['called' => 'Called by Dipsons', 'docs_pending' => 'Documents pending', 'in_process' => 'Work in process', 'done' => 'Completed', 'cancelled' => 'Cancelled', 'new' => 'Re-opened'][$st];
                add_note($eid, $public, 'status', admin_name(), true);
            }
            if (post('assigned_to') !== $enq['assigned_to'] && post('assigned_to') !== '') add_note($eid, 'Assigned to ' . post('assigned_to'), 'assign', admin_name());
            $cb = post('callback_at') ? date('Y-m-d H:i:s', strtotime(post('callback_at'))) : null;
            q('UPDATE enquiries SET status=?, assigned_to=?, callback_at=?, charges=?, payment_status=?, updated_at=NOW() WHERE id=?',
              [isset(STATUSES[$st]) ? $st : $enq['status'], post('assigned_to'), $cb, post('charges'), post('payment_status', 'pending'), $eid]);
            flash('Enquiry updated.');
        } elseif ($act === 'note' && post('note') !== '') {
            add_note($eid, post('note'), 'note', admin_name(), (bool)post('public'));
            flash('Note added.');
        } elseif ($act === 'wa_log') {
            add_note($eid, 'WhatsApp sent: ' . post('template'), 'wa', admin_name());
            header('Content-Type: application/json'); echo '{"ok":true}'; exit;
        } elseif ($act === 'upload') {
            $n = 0; foreach (files_list('docs') as $f) if (save_upload($f, $eid, (int)$enq['customer_id'], post('label') ?: 'Document')) $n++;
            flash($n ? "$n document(s) uploaded." : 'Upload failed — only photos/PDF up to 10 MB.', $n ? 'ok' : 'err');
        } elseif ($act === 'delete_doc') {
            $d = row('SELECT * FROM documents WHERE id=? AND enquiry_id=?', [(int)post('doc'), $eid]);
            if ($d) { @unlink(UPLOAD_DIR . '/' . $d['filename']); q('DELETE FROM documents WHERE id=?', [$d['id']]); flash('Document deleted.'); }
        } elseif ($act === 'reminders') {
            q('UPDATE enquiries SET remind=? WHERE id=?', [post('remind') ? 1 : 0, $eid]);
            if ($enq['vehicle_id']) {
                q('UPDATE vehicles SET insurance_expiry=?, puc_expiry=?, tax_expiry=?, fitness_expiry=?, permit_expiry=? WHERE id=?', [
                    date_or_null(post('insurance_expiry')), date_or_null(post('puc_expiry')), date_or_null(post('tax_expiry')),
                    date_or_null(post('fitness_expiry')), date_or_null(post('permit_expiry')), $enq['vehicle_id']]);
            }
            flash('Reminders saved.');
        } elseif ($act === 'edit') {
            q('UPDATE enquiries SET name=?, vehicle_type=?, vehicle_number=?, vehicle_model=?, message=? WHERE id=?',
              [post('name'), post('vehicle_type'), fmt_vehicle(post('vehicle_number')), post('vehicle_model'), post('message'), $eid]);
            flash('Details saved.');
        }
        redirect($back);
    }

    $docs = rows('SELECT * FROM documents WHERE enquiry_id=? ORDER BY id', [$eid]);
    $notes = rows('SELECT * FROM enquiry_notes WHERE enquiry_id=? ORDER BY id', [$eid]);
    $veh = $enq['vehicle_id'] ? row('SELECT * FROM vehicles WHERE id=?', [$enq['vehicle_id']]) : null;
    $svcDocs = [];
    foreach (rows('SELECT s.documents FROM enquiry_services es JOIN services s ON s.id=es.service_id WHERE es.enquiry_id=?', [$eid]) as $s) foreach (lines($s['documents']) as $l) $svcDocs[$l] = 1;
    $other = rows('SELECT code, service_names, status, created_at FROM enquiries WHERE mobile=? AND id<>? ORDER BY id DESC LIMIT 5', [$enq['mobile'], $eid]);
    render('admin/enquiry', $base + ['title' => 'Enquiry ' . $enq['code'], 'nav' => 'enq', 'enq' => $enq, 'docs' => $docs, 'notes' => $notes, 'veh' => $veh, 'svcDocs' => array_keys($svcDocs), 'other' => $other], $L);
}

if (preg_match('#^/admin/file/(\d+)$#', $path, $m)) {
    $d = row('SELECT * FROM documents WHERE id=?', [(int)$m[1]]);
    if (!$d) not_found();
    require_once APP . '/file_send.php';
    send_file($d, get('dl') === '1');
}

// ================= Customers
if ($path === '/admin/customers') {
    $qq = get('q'); $w = '1=1'; $a = [];
    if ($qq !== '') {
        $w = '(c.name LIKE ? OR c.mobile LIKE ? OR EXISTS (SELECT 1 FROM vehicles v WHERE v.customer_id=c.id AND v.number LIKE ?))';
        $a = ['%' . $qq . '%', '%' . preg_replace('/\D/', '', $qq) . '%', '%' . clean_vehicle($qq) . '%'];
        if (preg_replace('/\D/', '', $qq) === '') $a[1] = '%' . $qq . '%';
    }
    $page = max(1, (int)get('p', 1)); $per = 40;
    $total = (int)val("SELECT COUNT(*) FROM customers c WHERE $w", $a);
    $list = rows("SELECT c.*, (SELECT COUNT(*) FROM vehicles v WHERE v.customer_id=c.id) vc, (SELECT COUNT(*) FROM enquiries e WHERE e.customer_id=c.id) ec,
                  (SELECT GROUP_CONCAT(number SEPARATOR ', ') FROM vehicles v WHERE v.customer_id=c.id) vnums
                  FROM customers c WHERE $w ORDER BY c.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $a);
    render('admin/customers', $base + ['title' => 'Customers', 'nav' => 'cust', 'list' => $list, 'total' => $total, 'page' => $page, 'per' => $per], $L);
}

if (preg_match('#^/admin/customers/(\d+)$#', $path, $m)) {
    $c = row('SELECT * FROM customers WHERE id=?', [(int)$m[1]]);
    if (!$c) not_found();
    if (is_post()) {
        check_csrf();
        $act = post('action');
        if ($act === 'customer') {
            q('UPDATE customers SET name=?, city=?, notify_whatsapp=? WHERE id=?', [post('name'), post('city'), post('notify') ? 1 : 0, $c['id']]);
        } elseif ($act === 'vehicle') {
            $vid = (int)post('vid');
            $vals = [fmt_vehicle(post('number')), post('type'), post('model'), date_or_null(post('insurance_expiry')), date_or_null(post('puc_expiry')), date_or_null(post('tax_expiry')), date_or_null(post('fitness_expiry')), date_or_null(post('permit_expiry'))];
            if ($vals[0] === '') { flash('Vehicle number required.', 'err'); redirect('/admin/customers/' . $c['id']); }
            try {
                if ($vid) q('UPDATE vehicles SET number=?,type=?,model=?,insurance_expiry=?,puc_expiry=?,tax_expiry=?,fitness_expiry=?,permit_expiry=? WHERE id=? AND customer_id=?', [...$vals, $vid, $c['id']]);
                else q('INSERT INTO vehicles (number,type,model,insurance_expiry,puc_expiry,tax_expiry,fitness_expiry,permit_expiry,customer_id) VALUES (?,?,?,?,?,?,?,?,?)', [...$vals, $c['id']]);
            } catch (PDOException $e) { flash('This vehicle number already exists for the customer.', 'err'); redirect('/admin/customers/' . $c['id']); }
        } elseif ($act === 'delete_vehicle') {
            q('DELETE FROM vehicles WHERE id=? AND customer_id=?', [(int)post('vid'), $c['id']]);
        }
        flash('Saved.');
        redirect('/admin/customers/' . $c['id']);
    }
    $veh = rows('SELECT * FROM vehicles WHERE customer_id=? ORDER BY id', [$c['id']]);
    $enqs = rows('SELECT * FROM enquiries WHERE customer_id=? OR mobile=? ORDER BY id DESC', [$c['id'], $c['mobile']]);
    $docs = rows('SELECT * FROM documents WHERE customer_id=? ORDER BY id DESC', [$c['id']]);
    render('admin/customer', $base + ['title' => $c['name'] ?: fmt_mobile($c['mobile']), 'nav' => 'cust', 'c' => $c, 'veh' => $veh, 'enqs' => $enqs, 'docs' => $docs], $L);
}

// ================= Reminders
if ($path === '/admin/reminders') {
    if (is_post()) {
        check_csrf();
        q('INSERT INTO reminder_log (vehicle_id,kind,due_date,by_name) VALUES (?,?,?,?)', [(int)post('vid'), post('kind'), post('due'), admin_name()]);
        if (post('ajax')) { header('Content-Type: application/json'); echo '{"ok":true}'; exit; }
        flash('Marked as sent.'); redirect('/admin/reminders?' . http_build_query($_GET));
    }
    $days = (int)get('days', 30); if (!in_array($days, [7, 15, 30, 60], true)) $days = 30;
    $list = renewals_due($days, 500);
    if (get('kind') && isset(EXPIRY_KINDS[get('kind')])) $list = array_values(array_filter($list, fn($r) => $r['kind'] === get('kind')));
    if (get('show') === 'unsent') $list = array_values(array_filter($list, fn($r) => !$r['last_sent']));
    render('admin/reminders', $base + ['title' => 'Reminders', 'nav' => 'rem', 'list' => $list, 'days' => $days], $L);
}

// ================= Services & fees
if ($path === '/admin/services') {
    if (is_post()) {
        check_csrf();
        if (post('action') === 'toggle') q('UPDATE services SET active=1-active WHERE id=?', [(int)post('id')]);
        if (post('action') === 'order') foreach ((array)($_POST['sort'] ?? []) as $id => $s) q('UPDATE services SET sort=? WHERE id=?', [(int)$s, (int)$id]);
        flash('Saved.'); redirect('/admin/services');
    }
    $list = rows('SELECT s.*, c.name cat, (SELECT COUNT(*) FROM enquiry_services es WHERE es.service_id=s.id) n FROM services s LEFT JOIN service_categories c ON c.id=s.category_id ORDER BY c.sort, s.sort, s.id');
    render('admin/services', $base + ['title' => 'Services & fees', 'nav' => 'svc', 'list' => $list], $L);
}

if (preg_match('#^/admin/services/(new|\d+)$#', $path, $m)) {
    $s = $m[1] === 'new' ? null : row('SELECT * FROM services WHERE id=?', [(int)$m[1]]);
    if ($m[1] !== 'new' && !$s) not_found();
    if (is_post()) {
        check_csrf();
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(post('slug') ?: post('name'))), '-');
        $reserved = ['services', 'contact', 'about', 'enquiry', 'thank-you', 'reminder', 'admin', 'app', 'assets', 'health'];
        if (post('name') === '' || $slug === '' || in_array($slug, $reserved, true)) { flash('Please give a valid name / URL.', 'err'); redirect($path); }
        $vals = [(int)post('category_id') ?: null, $slug, post('name'), post('short_desc'), post('tagline'), post('tags'), post('what_we_do'), post('documents'), post('fee_note'), post('icon', 'file'), post('color', 'red'), (int)post('sort'), post('active') ? 1 : 0];
        try {
            if ($s) q('UPDATE services SET category_id=?,slug=?,name=?,short_desc=?,tagline=?,tags=?,what_we_do=?,documents=?,fee_note=?,icon=?,color=?,sort=?,active=? WHERE id=?', [...$vals, $s['id']]);
            else q('INSERT INTO services (category_id,slug,name,short_desc,tagline,tags,what_we_do,documents,fee_note,icon,color,sort,active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', $vals);
        } catch (PDOException $e) { flash('That URL is already used by another service.', 'err'); redirect($path); }
        flash('Service saved.'); redirect('/admin/services');
    }
    $cats = rows('SELECT * FROM service_categories ORDER BY sort');
    render('admin/service_edit', $base + ['title' => $s ? 'Edit ' . $s['name'] : 'Add service', 'nav' => 'svc', 's' => $s, 'cats' => $cats], $L);
}

// ================= Website content
if ($path === '/admin/content') {
    if (is_post()) {
        check_csrf();
        foreach (['business_name', 'business_tagline', 'phone1', 'phone2', 'whatsapp', 'address_line1', 'address_line2', 'timings', 'map_query', 'hero_title', 'hero_text', 'callback_promise', 'about_text', 'reminder_template'] as $k) {
            if (isset($_POST[$k])) set_setting($k, in_array($k, ['phone1', 'phone2', 'whatsapp'], true) ? clean_mobile(post($k)) : post($k));
        }
        $faq = []; foreach ((array)($_POST['faq_q'] ?? []) as $i => $qq) if (trim($qq) !== '') $faq[] = [trim($qq), trim($_POST['faq_a'][$i] ?? '')];
        set_setting('faq', json_encode($faq, JSON_UNESCAPED_UNICODE));
        $tpl = []; foreach ((array)($_POST['tpl_n'] ?? []) as $i => $n) if (trim($n) !== '') $tpl[] = [trim($n), trim($_POST['tpl_t'][$i] ?? '')];
        set_setting('wa_templates', json_encode($tpl, JSON_UNESCAPED_UNICODE));
        flash('Website content saved.'); redirect('/admin/content');
    }
    render('admin/content', $base + ['title' => 'Website content', 'nav' => 'content'], $L);
}

// ================= Settings (password, staff)
if ($path === '/admin/settings') {
    if (is_post()) {
        check_csrf();
        $act = post('action');
        if ($act === 'password') {
            if (!password_verify(post('current'), admin()['password_hash'])) flash('Current password is wrong.', 'err');
            elseif (strlen(post('new')) < 8) flash('New password must be at least 8 characters.', 'err');
            else { q('UPDATE admins SET password_hash=? WHERE id=?', [password_hash(post('new'), PASSWORD_DEFAULT), admin()['id']]); flash('Password changed.'); }
        } elseif ($act === 'staff' && is_owner()) {
            $u = strtolower(post('username'));
            if (!preg_match('/^[a-z0-9_.]{3,40}$/', $u) || strlen(post('password')) < 8 || post('name') === '') flash('Name, username (3+ letters) and password (8+) required.', 'err');
            elseif (val('SELECT COUNT(*) FROM admins WHERE username=?', [$u])) flash('Username already taken.', 'err');
            else { q('INSERT INTO admins (name,username,password_hash,role) VALUES (?,?,?,?)', [post('name'), $u, password_hash(post('password'), PASSWORD_DEFAULT), post('role') === 'owner' ? 'owner' : 'staff']); flash('Staff added.'); }
        } elseif ($act === 'toggle_staff' && is_owner() && (int)post('id') !== (int)admin()['id']) {
            q('UPDATE admins SET active=1-active WHERE id=?', [(int)post('id')]); flash('Updated.');
        } elseif ($act === 'reset_staff' && is_owner()) {
            if (strlen(post('password')) < 8) flash('Password must be at least 8 characters.', 'err');
            else { q('UPDATE admins SET password_hash=? WHERE id=?', [password_hash(post('password'), PASSWORD_DEFAULT), (int)post('id')]); flash('Password reset.'); }
        }
        redirect('/admin/settings');
    }
    $staff = rows('SELECT * FROM admins ORDER BY id');
    render('admin/settings', $base + ['title' => 'Settings', 'nav' => 'set', 'staff' => $staff], $L);
}

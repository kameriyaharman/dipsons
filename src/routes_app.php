<?php
// Customer mobile app (installable PWA). Login = mobile number + name, no OTP.
$L = 'app/layout';

function app_customer(): ?array {
    static $c = false;
    if ($c === false) $c = !empty($_SESSION['cust_id']) ? row('SELECT * FROM customers WHERE id=?', [$_SESSION['cust_id']]) : null;
    return $c;
}
function app_require(): array {
    $c = app_customer();
    if (!$c) redirect('/app/login');
    return $c;
}
function app_vehicles(int $cid): array { return rows('SELECT * FROM vehicles WHERE customer_id=? ORDER BY id', [$cid]); }
function app_enquiry(string $code, int $cid): array {
    $e = row('SELECT * FROM enquiries WHERE code=? AND customer_id=?', [$code, $cid]);
    if (!$e) not_found();
    return $e;
}

if ($path === '/app') {
    redirect(app_customer() ? '/app/home' : '/app/login');
}

if ($path === '/app/login') {
    if (app_customer()) redirect('/app/home');
    $err = '';
    if (is_post()) {
        check_csrf();
        $m = clean_mobile(post('mobile'));
        $name = post('name');
        if (!valid_mobile($m)) $err = 'Please enter a valid 10-digit mobile number.';
        else {
            $c = row('SELECT * FROM customers WHERE mobile=?', [$m]);
            if (!$c) {
                if ($name === '') $err = 'Please enter your name.';
                else { q('INSERT INTO customers (name,mobile,last_login_at) VALUES (?,?,NOW())', [$name, $m]); $id = (int)db()->lastInsertId(); }
            } else {
                $id = (int)$c['id'];
                q('UPDATE customers SET last_login_at=NOW(), name=IF(?<>"",?,name) WHERE id=?', [$name, $name, $id]);
            }
            if (!$err) {
                session_regenerate_id(true);
                $_SESSION['cust_id'] = $id;
                redirect('/app/home');
            }
        }
    }
    render('app/login', ['title' => 'Login', 'err' => $err, 'bare' => true], $L);
}

if ($path === '/app/guest') { // continue as guest → website enquiry form
    redirect('/contact');
}

if ($path === '/app/logout') {
    unset($_SESSION['cust_id']);
    redirect('/app/login');
}

$cust = app_require();
$cid = (int)$cust['id'];

if ($path === '/app/home') {
    $veh = app_vehicles($cid);
    // pick the soonest upcoming expiry for the alert
    $alert = null;
    foreach ($veh as $v) foreach (['insurance' => 'insurance_expiry', 'puc' => 'puc_expiry', 'tax' => 'tax_expiry', 'fitness' => 'fitness_expiry', 'permit' => 'permit_expiry'] as $k => $col) {
        if ($v[$col] !== null) { $d = days_left($v[$col]); if ($alert === null || $d < $alert['days']) $alert = ['days' => $d, 'kind' => $k, 'veh' => $v]; }
    }
    $open = (int)val("SELECT COUNT(*) FROM enquiries WHERE customer_id=? AND status NOT IN ('done','cancelled')", [$cid]);
    render('app/home', ['title' => 'Home', 'tab' => 'home', 'veh' => $veh, 'alert' => $alert, 'open' => $open], $L);
}

if ($path === '/app/services') {
    render('app/services', ['title' => 'All services', 'tab' => 'services', 'groups' => services_grouped()], $L);
}

if ($path === '/app/new') {
    if (is_post()) {
        check_csrf();
        $services = array_filter(array_map('intval', (array)($_POST['services'] ?? [])));
        if (!$services) { flash('Please select at least one service.', 'err'); redirect('/app/new'); }
        $files = [];
        foreach (['rc' => 'RC', 'policy' => 'Previous policy', 'id' => 'Aadhaar / PAN', 'other' => 'Other document'] as $field => $label) {
            foreach (files_list($field) as $i => $f) $files[$label . ($i ? ' ' . ($i + 1) : '')] = $f;
        }
        $r = create_enquiry([
            'name' => $cust['name'], 'mobile' => $cust['mobile'], 'services' => $services,
            'vehicle_type' => post('vehicle_type'), 'vehicle_number' => post('vehicle_number'), 'vehicle_model' => post('vehicle_model'),
            'insurance_expiry' => post('insurance_expiry'), 'puc_expiry' => post('puc_expiry'),
            'message' => post('message'), 'preferred' => post('preferred'), 'remind' => post('remind') ? 1 : 0,
            'source' => 'app',
        ], $files);
        redirect('/app/enquiry/' . $r['code'] . '?new=1');
    }
    $pre = get('service');
    render('app/new', ['title' => 'New enquiry', 'tab' => 'services', 'veh' => app_vehicles($cid), 'pre' => $pre, 'groups' => services_grouped()], $L);
}

if ($path === '/app/enquiries') {
    $f = get('f', 'all');
    $where = $f === 'open' ? " AND status NOT IN ('done','cancelled')" : ($f === 'done' ? " AND status IN ('done','cancelled')" : '');
    $list = rows("SELECT * FROM enquiries WHERE customer_id=? $where ORDER BY id DESC LIMIT 100", [$cid]);
    render('app/enquiries', ['title' => 'My enquiries', 'tab' => 'enq', 'list' => $list, 'f' => $f], $L);
}

if (preg_match('#^/app/enquiry/(DPS\d+)$#', $path, $m)) {
    $enq = app_enquiry($m[1], $cid);
    if (is_post()) {
        check_csrf();
        $n = 0;
        foreach (files_list('docs') as $f) if (save_upload($f, (int)$enq['id'], $cid, 'Uploaded from app')) $n++;
        if ($n) { add_note((int)$enq['id'], "$n document(s) uploaded by customer", 'status', $cust['name'], true); flash('Documents uploaded. Thank you!'); }
        else flash('Please choose a photo or PDF (max 10 MB).', 'err');
        redirect('/app/enquiry/' . $enq['code']);
    }
    $notes = rows('SELECT * FROM enquiry_notes WHERE enquiry_id=? AND public=1 ORDER BY id', [$enq['id']]);
    $docs = rows('SELECT * FROM documents WHERE enquiry_id=? ORDER BY id', [$enq['id']]);
    render('app/enquiry', ['title' => 'Enquiry ' . $enq['code'], 'tab' => 'enq', 'enq' => $enq, 'notes' => $notes, 'docs' => $docs, 'isNew' => get('new') === '1'], $L);
}

if ($path === '/app/reminders') {
    render('app/reminders', ['title' => 'Reminders', 'tab' => 'rem', 'veh' => app_vehicles($cid)], $L);
}

if ($path === '/app/vehicle') {
    $id = (int)get('id');
    $v = $id ? row('SELECT * FROM vehicles WHERE id=? AND customer_id=?', [$id, $cid]) : null;
    if ($id && !$v) not_found();
    if (is_post()) {
        check_csrf();
        if (post('delete') && $v) {
            q('DELETE FROM vehicles WHERE id=?', [$v['id']]);
            flash('Vehicle removed.'); redirect('/app/reminders');
        }
        $num = fmt_vehicle(post('number'));
        if ($num === '') { flash('Please enter vehicle number.', 'err'); redirect('/app/vehicle' . ($id ? '?id=' . $id : '')); }
        $vals = [$num, post('type'), post('model'), date_or_null(post('insurance_expiry')), date_or_null(post('puc_expiry')), date_or_null(post('tax_expiry')), date_or_null(post('fitness_expiry')), date_or_null(post('permit_expiry'))];
        try {
            if ($v) q('UPDATE vehicles SET number=?,type=?,model=?,insurance_expiry=?,puc_expiry=?,tax_expiry=?,fitness_expiry=?,permit_expiry=? WHERE id=?', [...$vals, $v['id']]);
            else q('INSERT INTO vehicles (number,type,model,insurance_expiry,puc_expiry,tax_expiry,fitness_expiry,permit_expiry,customer_id) VALUES (?,?,?,?,?,?,?,?,?)', [...$vals, $cid]);
        } catch (PDOException $e) {
            flash('This vehicle is already added.', 'err'); redirect('/app/reminders');
        }
        flash('Vehicle saved. We will remind you before expiry.');
        redirect('/app/reminders');
    }
    render('app/vehicle', ['title' => $v ? 'Edit vehicle' : 'Add vehicle', 'tab' => 'rem', 'v' => $v], $L);
}

if ($path === '/app/profile') {
    if (is_post()) {
        check_csrf();
        q('UPDATE customers SET name=?, city=?, notify_whatsapp=? WHERE id=?', [post('name') ?: $cust['name'], post('city'), post('notify') ? 1 : 0, $cid]);
        flash('Profile updated.');
        redirect('/app/profile');
    }
    $vc = (int)val('SELECT COUNT(*) FROM vehicles WHERE customer_id=?', [$cid]);
    $dc = (int)val('SELECT COUNT(*) FROM documents WHERE customer_id=?', [$cid]);
    render('app/profile', ['title' => 'Profile', 'tab' => 'me', 'cust' => $cust, 'vc' => $vc, 'dc' => $dc], $L);
}

if ($path === '/app/documents') {
    $docs = rows('SELECT d.*, e.code FROM documents d LEFT JOIN enquiries e ON e.id=d.enquiry_id WHERE d.customer_id=? ORDER BY d.id DESC', [$cid]);
    render('app/documents', ['title' => 'My documents', 'tab' => 'me', 'docs' => $docs], $L);
}

if (preg_match('#^/app/file/(\d+)$#', $path, $m)) {
    $d = row('SELECT * FROM documents WHERE id=? AND customer_id=?', [(int)$m[1], $cid]);
    if (!$d) not_found();
    require_once APP . '/file_send.php';
    send_file($d);
}

<?php
// ---------------------------------------------------------------
// Dipsons core: config, database, helpers, rendering, auth
// ---------------------------------------------------------------
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);

date_default_timezone_set('Asia/Kolkata');

// ---- Config: environment variables (Railway) or config.php (shared hosting)
$CONFIG = [];
if (is_file(ROOT . '/config.php')) {
    $CONFIG = require ROOT . '/config.php';
}
function cfg(string $key, $default = null) {
    global $CONFIG;
    $env = getenv($key);
    if ($env !== false && $env !== '') return $env;
    return $CONFIG[$key] ?? $default;
}

define('UPLOAD_DIR', rtrim((string)cfg('UPLOAD_DIR', ROOT . '/storage/uploads'), '/'));
define('DEBUG', (bool)cfg('APP_DEBUG', false));

ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);

// ---- Session (long-lived so app users stay logged in)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params(['lifetime' => 60 * 60 * 24 * 180, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $secure]);
ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 180));
$sessDir = ROOT . '/storage/sessions';
if (!is_dir($sessDir)) @mkdir($sessDir, 0775, true);
if (is_dir($sessDir) && is_writable($sessDir)) session_save_path($sessDir);
session_name('dipsons');
session_start();

// ---- Database
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    // Railway MySQL exposes MYSQL_URL / MYSQLHOST etc.
    $url = cfg('DATABASE_URL') ?: cfg('MYSQL_URL');
    if ($url) {
        $p = parse_url($url);
        $host = $p['host']; $port = $p['port'] ?? 3306;
        $user = urldecode($p['user'] ?? ''); $pass = urldecode($p['pass'] ?? '');
        $name = ltrim($p['path'] ?? '', '/');
    } else {
        $host = cfg('DB_HOST', cfg('MYSQLHOST', 'localhost'));
        $port = cfg('DB_PORT', cfg('MYSQLPORT', 3306));
        $user = cfg('DB_USER', cfg('MYSQLUSER', 'root'));
        $pass = cfg('DB_PASS', cfg('MYSQLPASSWORD', ''));
        $name = cfg('DB_NAME', cfg('MYSQLDATABASE', 'dipsons'));
    }
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '+05:30'");
    return $pdo;
}
function q(string $sql, array $args = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st;
}
function row(string $sql, array $args = []): ?array { $r = q($sql, $args)->fetch(); return $r ?: null; }
function rows(string $sql, array $args = []): array { return q($sql, $args)->fetchAll(); }
function val(string $sql, array $args = []) { return q($sql, $args)->fetchColumn(); }

// ---- First-run install: create tables + seed content
function ensure_installed(): void {
    $ok = false;
    try { $ok = (bool)db()->query("SHOW TABLES LIKE 'services'")->fetch(); } catch (Throwable $e) { throw $e; }
    if ($ok && val('SELECT COUNT(*) FROM services') > 0) return;

    $schema = file_get_contents(APP . '/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', $schema)))) as $stmt) {
        db()->exec($stmt);
    }
    $seed = require APP . '/seed.php';
    $catIds = [];
    foreach ($seed['categories'] as [$slug, $name, $sort]) {
        q('INSERT IGNORE INTO service_categories (slug,name,sort) VALUES (?,?,?)', [$slug, $name, $sort]);
        $catIds[$slug] = (int)val('SELECT id FROM service_categories WHERE slug=?', [$slug]);
    }
    $i = 0;
    foreach ($seed['services'] as $s) {
        [$slug, $cat, $name, $short, $tagline, $tags, $what, $docs, $icon, $color, $nav] = $s;
        q('INSERT IGNORE INTO services (category_id,slug,name,short_desc,tagline,tags,what_we_do,documents,icon,color,sort,show_in_nav) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
          [$catIds[$cat], $slug, $name, $short, $tagline, $tags, $what, $docs, $icon, $color, ++$i * 10, $nav]);
    }
    foreach ($seed['settings'] as $k => $v) {
        q('INSERT IGNORE INTO settings (k,v) VALUES (?,?)', [$k, $v]);
    }
    // Optional first admin from environment
    $u = cfg('ADMIN_USER'); $p = cfg('ADMIN_PASS');
    if ($u && $p && !val('SELECT COUNT(*) FROM admins')) {
        q('INSERT INTO admins (name,username,password_hash,role) VALUES (?,?,?,?)', ['Owner', $u, password_hash($p, PASSWORD_DEFAULT), 'owner']);
    }
}

// ---- Settings
function setting(string $k, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT k,v FROM settings') as $r) $cache[$r['k']] = (string)$r['v'];
    }
    return $cache[$k] ?? $default;
}
function set_setting(string $k, string $v): void {
    q('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)', [$k, $v]);
}
function setting_json(string $k): array { $d = json_decode(setting($k, '[]'), true); return is_array($d) ? $d : []; }

// ---- Helpers
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function post(string $k, $d = '') { $v = $_POST[$k] ?? $d; return is_string($v) ? trim($v) : $v; }
function get(string $k, $d = '') { $v = $_GET[$k] ?? $d; return is_string($v) ? trim($v) : $v; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function check_csrf(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expired. Please go back, refresh the page and try again.');
    }
}
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function clean_mobile(string $m): string {
    $d = preg_replace('/\D/', '', $m);
    if (strlen($d) > 10 && str_starts_with($d, '91')) $d = substr($d, -10);
    if (strlen($d) === 11 && $d[0] === '0') $d = substr($d, 1);
    return $d;
}
function valid_mobile(string $m): bool { return (bool)preg_match('/^[6-9]\d{9}$/', $m); }
function clean_vehicle(string $v): string { return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v)); }
function fmt_vehicle(string $v): string {
    $v = clean_vehicle($v);
    if (preg_match('/^([A-Z]{2})(\d{1,2})([A-Z]{0,3})(\d{1,4})$/', $v, $m)) {
        return trim($m[1] . ' ' . $m[2] . ' ' . $m[3] . ' ' . $m[4]);
    }
    return $v;
}
function fmt_mobile(string $m): string { return $m ? '+91 ' . substr($m, 0, 5) . ' ' . substr($m, 5) : ''; }
function fmt_date(?string $d, string $f = 'd M Y'): string { return $d ? date($f, strtotime($d)) : ''; }
function fmt_time(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    if (date('Y-m-d', $t) === date('Y-m-d')) return date('h:i A', $t);
    if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) return 'Yesterday';
    return date('d M', $t);
}
function date_or_null(string $d): ?string {
    $d = trim($d); if ($d === '') return null;
    $t = strtotime($d); return $t ? date('Y-m-d', $t) : null;
}
function wa_link(string $mobile, string $text = ''): string {
    $m = clean_mobile($mobile);
    return 'https://wa.me/91' . $m . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}
function tel(string $m): string { return 'tel:+91' . clean_mobile($m); }
function lines(?string $s): array { return array_values(array_filter(array_map('trim', explode("\n", (string)$s)))); }
function csv_list(?string $s): array { return array_values(array_filter(array_map('trim', explode(',', (string)$s)))); }
function url_path(): string { return '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/'); }
function asset(string $p): string {
    $f = ROOT . '/assets/' . $p;
    return '/assets/' . $p . (is_file($f) ? '?v=' . filemtime($f) : '');
}

const VEHICLE_TYPES = ['Two-wheeler', 'Car / Jeep', 'Auto / E-rickshaw', 'Truck / Pickup', 'Bus', 'Tractor', 'Other'];
const VEHICLE_ICONS = ['Two-wheeler' => 'bike', 'Car / Jeep' => 'car', 'Auto / E-rickshaw' => 'auto', 'Truck / Pickup' => 'truck', 'Bus' => 'bus', 'Tractor' => 'tractor', 'Other' => 'wrench'];
const STATUSES = ['new' => 'New', 'called' => 'Called', 'docs_pending' => 'Documents pending', 'in_process' => 'In process', 'done' => 'Done', 'cancelled' => 'Cancelled'];
const SOURCES = ['website' => 'Website', 'app' => 'App', 'whatsapp' => 'WhatsApp', 'call' => 'Call', 'walkin' => 'Walk-in'];
const EXPIRY_KINDS = ['insurance' => 'Insurance', 'puc' => 'PUC', 'tax' => 'Road tax', 'fitness' => 'Fitness', 'permit' => 'Permit'];

function status_badge(string $s): string {
    return '<span class="badge st-' . e($s) . '">' . e(STATUSES[$s] ?? $s) . '</span>';
}
function expiry_state(?string $d): array {
    if (!$d) return ['', ''];
    $days = (int)floor((strtotime($d) - strtotime(date('Y-m-d'))) / 86400);
    if ($days < 0) return ['expired', 'Expired'];
    if ($days <= 30) return ['due', 'Due soon'];
    return ['valid', 'Valid'];
}
function days_left(?string $d): ?int { return $d ? (int)floor((strtotime($d) - strtotime(date('Y-m-d'))) / 86400) : null; }

// ---- Services
function services_all(bool $activeOnly = true): array {
    static $c = [];
    $k = $activeOnly ? 1 : 0;
    if (!isset($c[$k])) {
        $c[$k] = rows('SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.sort AS category_sort FROM services s LEFT JOIN service_categories c ON c.id=s.category_id'
            . ($activeOnly ? ' WHERE s.active=1' : '') . ' ORDER BY c.sort, s.sort, s.id');
    }
    return $c[$k];
}
function services_grouped(): array {
    $g = [];
    foreach (services_all() as $s) $g[$s['category_name'] ?: 'Other services'][] = $s;
    return $g;
}
function service_by_slug(string $slug): ?array {
    return row('SELECT s.*, c.slug AS category_slug, c.name AS category_name FROM services s LEFT JOIN service_categories c ON c.id=s.category_id WHERE s.slug=? AND s.active=1', [$slug]);
}

// ---- Enquiry creation (shared by website, app, admin)
function create_enquiry(array $d, array $files = []): array {
    $mobile = clean_mobile($d['mobile'] ?? '');
    $name = trim($d['name'] ?? '');
    $vnum = fmt_vehicle($d['vehicle_number'] ?? '');
    $serviceIds = array_map('intval', (array)($d['services'] ?? []));
    $svcRows = $serviceIds ? rows('SELECT id,name FROM services WHERE id IN (' . implode(',', array_fill(0, count($serviceIds), '?')) . ')', $serviceIds) : [];
    $svcNames = implode(', ', array_column($svcRows, 'name'));

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // customer
        $cust = row('SELECT * FROM customers WHERE mobile=?', [$mobile]);
        if (!$cust) {
            q('INSERT INTO customers (name,mobile,city) VALUES (?,?,?)', [$name, $mobile, $d['city'] ?? '']);
            $custId = (int)$pdo->lastInsertId();
        } else {
            $custId = (int)$cust['id'];
            if ($name && !$cust['name']) q('UPDATE customers SET name=? WHERE id=?', [$name, $custId]);
        }
        // vehicle
        $vehId = null;
        $ins = date_or_null($d['insurance_expiry'] ?? '');
        $puc = date_or_null($d['puc_expiry'] ?? '');
        if ($vnum !== '') {
            $veh = row('SELECT * FROM vehicles WHERE customer_id=? AND number=?', [$custId, $vnum]);
            if (!$veh) {
                q('INSERT INTO vehicles (customer_id,number,type,model,insurance_expiry,puc_expiry) VALUES (?,?,?,?,?,?)',
                  [$custId, $vnum, $d['vehicle_type'] ?? '', $d['vehicle_model'] ?? '', $ins, $puc]);
                $vehId = (int)$pdo->lastInsertId();
            } else {
                $vehId = (int)$veh['id'];
                q('UPDATE vehicles SET type=IF(?<>"",?,type), model=IF(?<>"",?,model), insurance_expiry=COALESCE(?,insurance_expiry), puc_expiry=COALESCE(?,puc_expiry) WHERE id=?',
                  [$d['vehicle_type'] ?? '', $d['vehicle_type'] ?? '', $d['vehicle_model'] ?? '', $d['vehicle_model'] ?? '', $ins, $puc, $vehId]);
            }
        }
        q('INSERT INTO enquiries (customer_id,vehicle_id,name,mobile,vehicle_type,vehicle_number,vehicle_model,service_names,insurance_expiry,puc_expiry,message,address,preferred,remind,source,status)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $custId, $vehId, $name, $mobile, $d['vehicle_type'] ?? '', $vnum, $d['vehicle_model'] ?? '', $svcNames,
            $ins, $puc, $d['message'] ?? '', $d['address'] ?? '', $d['preferred'] ?? '', !empty($d['remind']) ? 1 : 0,
            $d['source'] ?? 'website', $d['status'] ?? 'new',
        ]);
        $id = (int)$pdo->lastInsertId();
        $code = 'DPS' . (1000 + $id);
        q('UPDATE enquiries SET code=? WHERE id=?', [$code, $id]);
        foreach ($svcRows as $s) q('INSERT IGNORE INTO enquiry_services VALUES (?,?)', [$id, $s['id']]);
        $src = SOURCES[$d['source'] ?? 'website'] ?? 'website';
        add_note($id, 'Enquiry received from ' . strtolower($src), 'status', $d['by'] ?? '', true);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    foreach ($files as $label => $file) save_upload($file, $id, $custId, $label);
    return ['id' => $id, 'code' => $code, 'customer_id' => $custId];
}

function add_note(int $enquiryId, string $text, string $kind = 'note', string $by = '', bool $public = false): void {
    q('INSERT INTO enquiry_notes (enquiry_id,kind,text,by_name,public) VALUES (?,?,?,?,?)', [$enquiryId, $kind, $text, $by, $public ? 1 : 0]);
    q('UPDATE enquiries SET updated_at=NOW() WHERE id=?', [$enquiryId]);
}

const ALLOWED_UPLOADS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'application/pdf' => 'pdf'];

/** Save an uploaded file. $file is one entry of $_FILES (single file). */
function save_upload(array $file, ?int $enquiryId, ?int $customerId, string $label): ?int {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) return null;
    if ($file['size'] > 10 * 1024 * 1024) return null;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    if (!isset(ALLOWED_UPLOADS[$mime])) return null;
    $dir = UPLOAD_DIR . '/' . date('Y/m');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $name = bin2hex(random_bytes(12)) . '.' . ALLOWED_UPLOADS[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) return null;
    q('INSERT INTO documents (enquiry_id,customer_id,label,filename,original_name,mime,size) VALUES (?,?,?,?,?,?,?)',
      [$enquiryId, $customerId, $label, date('Y/m') . '/' . $name, mb_substr((string)$file['name'], 0, 200), $mime, (int)$file['size']]);
    return (int)db()->lastInsertId();
}
/** Normalise a multi-file input ($_FILES['x'] with arrays) into a list of single files. */
function files_list(string $field): array {
    $f = $_FILES[$field] ?? null; if (!$f) return [];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    return $out;
}
function human_size(int $b): string { return $b > 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, round($b / 1024)) . ' KB'; }

function fill_template(string $tpl, array $vars): string {
    foreach ($vars as $k => $v) $tpl = str_replace('{' . $k . '}', (string)$v, $tpl);
    return $tpl;
}

// ---- Rendering
function view(string $name, array $data = []): string {
    extract($data, EXTR_SKIP);
    ob_start();
    include APP . '/views/' . $name . '.php';
    return (string)ob_get_clean();
}
function render(string $name, array $data = [], ?string $layout = null): never {
    $content = view($name, $data);
    if ($layout) $content = view($layout, $data + ['content' => $content]);
    echo $content;
    exit;
}
function not_found(): never {
    http_response_code(404);
    render('site/404', ['title' => 'Page not found'], 'site/layout');
}

// ---- Icons (inline SVG, stroke style)
function icon(string $n, int $size = 20, string $cls = ''): string {
    static $p = [
        'shield' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
        'leaf' => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15"/><path d="M5 19c3-5 6-7 10-9"/>',
        'receipt' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/>',
        'badge' => '<path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6z"/><path d="M9.5 12.5l2 2 3.5-4"/>',
        'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
        'swap' => '<path d="M4 8h14l-4-4M20 16H6l4 4"/>',
        'doc' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
        'building' => '<path d="M4 21V5l8-2v18M12 8l8 2v11M8 9v.01M8 13v.01M8 17v.01M16 13v.01M16 17v.01M2 21h20"/>',
        'rto' => '<circle cx="12" cy="12" r="9"/><text x="12" y="15.5" text-anchor="middle" font-size="8" font-weight="700" stroke="none" fill="currentColor">RTO</text>',
        'idcard' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.5-1.5 1.5-2 3-2s2.5.5 3 2M14 10h4M14 14h3"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8" r="1.5"/>',
        'pin' => '<path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
        'gauge' => '<path d="M4 17a8 8 0 1116 0"/><path d="M12 17l4-6"/><circle cx="12" cy="17" r="1"/>',
        'satellite' => '<path d="M5 13a6 6 0 006 6M5 9a10 10 0 0010 10"/><circle cx="6" cy="18" r="1.5"/><path d="M13 5l6 6-3 3-6-6z"/>',
        'wrench' => '<path d="M14.5 6.5a4 4 0 00-5 5L4 17l3 3 5.5-5.5a4 4 0 005-5l-2.5 2.5-2.5-.5-.5-2.5z"/>',
        'garage' => '<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-8h10v8M7 16h10"/>',
        'car' => '<path d="M5 16l1.5-5a2 2 0 012-1.5h7a2 2 0 012 1.5L19 16"/><rect x="3" y="16" width="18" height="4" rx="1"/><circle cx="7" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/>',
        'droplet' => '<path d="M12 3s6 7 6 11a6 6 0 01-12 0c0-4 6-11 6-11z"/>',
        'bike' => '<circle cx="6" cy="16" r="3.5"/><circle cx="18" cy="16" r="3.5"/><path d="M6 16l4-7h5l3 7M10 9l3 7h-3"/>',
        'auto' => '<path d="M4 17V9a4 4 0 014-4h6l5 6v6"/><path d="M4 11h15"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
        'truck' => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
        'bus' => '<rect x="4" y="3" width="16" height="15" rx="2"/><path d="M4 11h16M8 21v-3M16 21v-3"/><circle cx="8" cy="14.5" r=".8"/><circle cx="16" cy="14.5" r=".8"/>',
        'tractor' => '<circle cx="7" cy="16" r="4"/><circle cx="18" cy="17" r="2.5"/><path d="M7 12V6h6l2 6h4v3M13 6v6"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
        'whatsapp' => '<path d="M4 20l1.3-4A8 8 0 1112 20a8 8 0 01-4-1.1z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 01-2-2l.8-1-1-2z"/>',
        'send' => '<path d="M21 3L10 14M21 3l-7 18-4-7-7-4z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<path d="M5 12l5 5 9-10"/>',
        'bell' => '<path d="M6 16V11a6 6 0 0112 0v5l2 2H4z"/><path d="M10 20a2 2 0 004 0"/>',
        'home' => '<path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>',
        'grid' => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4h6v3H9zM9 12h6M9 16h4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c.8-3.5 3.5-5 7-5s6.2 1.5 7 5M16 4a3.5 3.5 0 010 7M18 15c2 .6 3.3 2.2 4 5"/>',
        'chevron' => '<path d="M9 5l7 7-7 7"/>',
        'back' => '<path d="M15 5l-7 7 7 7"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>',
        'download' => '<path d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 00-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 00-2-1.2L14 3h-4l-.5 2.6a7 7 0 00-2 1.2l-2.4-1-2 3.4 2 1.6a7 7 0 000 2.4l-2 1.6 2 3.4 2.4-1a7 7 0 002 1.2L10 21h4l.5-2.6a7 7 0 002-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2z"/>',
        'layers' => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/>',
        'dashboard' => '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/>',
        'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'x' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'filter' => '<path d="M4 5h16l-6 8v6l-4-2v-4z"/>',
        'file' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'map' => '<path d="M9 4l-6 2v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
        'zap' => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
        'chat' => '<path d="M4 5h16v11H8l-4 4z"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
    ];
    $path = $p[$n] ?? $p['file'];
    return '<svg class="ico ' . $cls . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

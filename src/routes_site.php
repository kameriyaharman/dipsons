<?php
// Public website routes
$L = 'site/layout';

if ($path === '/') {
    render('site/home', ['title' => null, 'groups' => services_grouped()], $L);
}

// Android app (Trusted Web Activity) — proves the app and the website belong together
if ($path === '/.well-known/assetlinks.json') {
    header('Content-Type: application/json');
    echo json_encode([[
        'relation' => ['delegate_permission/common.handle_all_urls'],
        'target' => ['namespace' => 'android_app', 'package_name' => cfg('ANDROID_PACKAGE', 'in.dipsons.app'),
                     'sha256_cert_fingerprints' => array_map('trim', explode(',', (string)cfg('ANDROID_SHA256', 'DE:40:C8:6F:84:FF:63:12:9C:2B:FB:30:C9:82:35:A7:95:8F:F8:30:AB:AC:99:42:A1:DC:74:F6:60:6F:FB:5E')))],
    ]], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($path === '/services') {
    render('site/services', ['title' => 'All services', 'groups' => services_grouped()], $L);
}

if ($path === '/about') {
    render('site/about', ['title' => 'About us'], $L);
}

if ($path === '/contact') {
    render('site/contact', ['title' => 'Contact & enquiry', 'pre' => get('service')], $L);
}

if ($path === '/enquiry' && is_post()) {
    check_csrf();
    $back = $_SERVER['HTTP_REFERER'] ?? '/contact';
    if (post('website') !== '') redirect('/'); // honeypot (bots fill hidden field)
    $mobile = clean_mobile(post('mobile'));
    $services = array_filter(array_map('intval', (array)($_POST['services'] ?? [])));
    $errors = [];
    if (post('name') === '') $errors[] = 'Please enter your name.';
    if (!valid_mobile($mobile)) $errors[] = 'Please enter a valid 10-digit mobile number.';
    if (!$services) $errors[] = 'Please select at least one service.';
    if ($errors) {
        flash(implode(' ', $errors), 'err');
        $_SESSION['old'] = $_POST;
        redirect($back . (str_contains($back, '#') ? '' : '#enquiry'));
    }
    $files = [];
    foreach (files_list('docs') as $i => $f) $files['Document ' . ($i + 1)] = $f;
    $r = create_enquiry([
        'name' => post('name'), 'mobile' => $mobile, 'services' => $services,
        'vehicle_type' => post('vehicle_type'), 'vehicle_number' => post('vehicle_number'),
        'insurance_expiry' => post('expiry'), 'message' => post('message'),
        'source' => 'website', 'remind' => 1,
    ], $files);
    $_SESSION['last_enquiry'] = $r['code'];
    redirect('/thank-you?c=' . urlencode($r['code']));
}

if ($path === '/thank-you') {
    $code = get('c');
    $enq = ($code && ($_SESSION['last_enquiry'] ?? '') === $code) ? row('SELECT * FROM enquiries WHERE code=?', [$code]) : null;
    if (!$enq) redirect('/');
    render('site/thanks', ['title' => 'Enquiry received', 'enq' => $enq], $L);
}

if ($path === '/reminder' && is_post()) {
    check_csrf();
    if (post('website') !== '') redirect('/');
    $mobile = clean_mobile(post('mobile'));
    $vnum = fmt_vehicle(post('vehicle_number'));
    if (!valid_mobile($mobile) || $vnum === '') {
        flash('Please enter a valid vehicle number and 10-digit mobile number.', 'err');
        redirect('/#reminder');
    }
    $cust = row('SELECT id FROM customers WHERE mobile=?', [$mobile]);
    $custId = $cust ? (int)$cust['id'] : 0;
    if (!$custId) { q('INSERT INTO customers (name,mobile) VALUES (?,?)', [post('name'), $mobile]); $custId = (int)db()->lastInsertId(); }
    $ins = date_or_null(post('insurance_expiry')); $puc = date_or_null(post('puc_expiry'));
    $v = row('SELECT id FROM vehicles WHERE customer_id=? AND number=?', [$custId, $vnum]);
    if ($v) q('UPDATE vehicles SET insurance_expiry=COALESCE(?,insurance_expiry), puc_expiry=COALESCE(?,puc_expiry) WHERE id=?', [$ins, $puc, $v['id']]);
    else q('INSERT INTO vehicles (customer_id,number,insurance_expiry,puc_expiry) VALUES (?,?,?,?)', [$custId, $vnum, $ins, $puc]);
    flash('Done! Your vehicle ' . $vnum . ' is registered — we will remind you on WhatsApp before expiry.');
    redirect('/#reminder');
}

// Service pages: /services/insurance and the short form /insurance
$slug = null;
if (preg_match('#^/services/([a-z0-9-]+)$#', $path, $m)) $slug = $m[1];
elseif (preg_match('#^/([a-z0-9-]+)$#', $path, $m)) $slug = $m[1];
if ($slug && ($svc = service_by_slug($slug))) {
    render('site/service', ['title' => $svc['name'], 'svc' => $svc, 'groups' => services_grouped()], $L);
}

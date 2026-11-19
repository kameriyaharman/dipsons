<?php
// Stream an uploaded document to an authorised user.
function send_file(array $d, bool $download = false): never {
    $path = realpath(UPLOAD_DIR . '/' . $d['filename']);
    if (!$path || !str_starts_with($path, realpath(UPLOAD_DIR)) || !is_file($path)) not_found();
    $name = $d['original_name'] ?: basename($path);
    header('Content-Type: ' . ($d['mime'] ?: 'application/octet-stream'));
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . str_replace('"', '', $name) . '"');
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
}

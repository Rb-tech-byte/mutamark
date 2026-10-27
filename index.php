<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)
);

// If request is for a static file that exists in public/, serve it directly
if ($uri !== '/' && !empty($uri)) {
    $publicPath = __DIR__ . '/public' . $uri;
    if (file_exists($publicPath) && !is_dir($publicPath)) {
        $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'eot'   => 'application/vnd.ms-fontobject',
            'json'  => 'application/json; charset=utf-8',
            'txt'   => 'text/plain; charset=utf-8',
        ];
        $contentType = $mimeTypes[$ext] ?? mime_content_type($publicPath) ?: 'application/octet-stream';
        header("Content-Type: {$contentType}");
        header('Content-Length: ' . filesize($publicPath));
        header('Cache-Control: public, max-age=31536000');
        readfile($publicPath);
        exit;
    }
}

require_once __DIR__ . '/public/index.php';

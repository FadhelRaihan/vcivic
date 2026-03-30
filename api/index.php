<?php

// 1. Tentukan folder /tmp yang diizinkan (writable) oleh Vercel
$tmpStorage = '/tmp/storage';
$tmpCache = '/tmp/cache';

$directories = [
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/logs',
    $tmpCache
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 2. Paksa Laravel menggunakan /tmp untuk semua file sementaranya
putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = $tmpStorage . '/framework/views';

putenv('APP_SERVICES_CACHE=' . $tmpCache . '/services.php');
$_ENV['APP_SERVICES_CACHE'] = $tmpCache . '/services.php';

putenv('APP_PACKAGES_CACHE=' . $tmpCache . '/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = $tmpCache . '/packages.php';

putenv('APP_CONFIG_CACHE=' . $tmpCache . '/config.php');
$_ENV['APP_CONFIG_CACHE'] = $tmpCache . '/config.php';

putenv('APP_ROUTES_CACHE=' . $tmpCache . '/routes.php');
$_ENV['APP_ROUTES_CACHE'] = $tmpCache . '/routes.php';

putenv('APP_EVENTS_CACHE=' . $tmpCache . '/events.php');
$_ENV['APP_EVENTS_CACHE'] = $tmpCache . '/events.php';

// 3. Jalankan aplikasi Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Timpa jalur direktori Storage bawaan Laravel ke /tmp
$app->useStoragePath($tmpStorage);

// 4. Proses Request (Dukung Laravel 10 dan 11)
if (method_exists($app, 'handleRequest')) {
    // Untuk Laravel 11
    $app->handleRequest(Illuminate\Http\Request::capture());
} else {
    // Untuk Laravel 10
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Illuminate\Http\Request::capture());
    $response->send();
    $kernel->terminate($request, $response);
}
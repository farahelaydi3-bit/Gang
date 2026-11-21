<?php

use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
$public = $root . '/public';
$storage = $root . '/storage/app/demo';

if (PHP_SAPI === 'cli-server') {
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    if (str_contains($path, "\0")) {
        http_response_code(400);
        return;
    }

    if (str_starts_with($path, '/storage/')) {
        $uploads = realpath($storage . '/app/public');
        $file = $uploads !== false ? realpath($uploads . '/' . substr($path, strlen('/storage/'))) : false;
        $mimeTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $mime = $file !== false ? ($mimeTypes[strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? null) : null;

        if ($file === false || ! str_starts_with($file, $uploads . DIRECTORY_SEPARATOR) || ! is_file($file) || $mime === null) {
            http_response_code(404);
            return;
        }

        header('Content-Type: ' . $mime);
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        return;
    }

    if ($path !== '/' && is_file($public . $path)) {
        return false;
    }

    // The built-in server's request variables do not always include process environment values.
    $_SERVER['LARAVEL_STORAGE_PATH'] = $storage;
    require $public . '/index.php';
    return;
}

require $root . '/vendor/autoload.php';

foreach ([$storage, $storage . '/app/public', $storage . '/framework/views'] as $directory) {
    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        throw new RuntimeException("Unable to create the demo storage directory: $directory");
    }
}

$database = $storage . '/gang-demo.sqlite';
if (! is_file($database) && ! touch($database)) {
    throw new RuntimeException('Unable to create the demo database.');
}

$php = [PHP_BINARY];
if (! extension_loaded('pdo_sqlite') && PHP_OS_FAMILY === 'Windows') {
    $php = [...$php, '-d', 'extension=pdo_sqlite'];
}

$url = 'http://127.0.0.1:8001';
$environment = [
    'APP_ENV' => 'local',
    'APP_URL' => $url,
    'APP_KEY' => 'base64:' . base64_encode(random_bytes(32)),
    'APP_PREVIOUS_KEYS' => '',
    'APP_CONFIG_CACHE' => $storage . '/config.php',
    'APP_ROUTES_CACHE' => $storage . '/routes.php',
    'LARAVEL_STORAGE_PATH' => $storage,
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $database,
    'DB_URL' => '',
    'DB_FOREIGN_KEYS' => 'true',
    'SESSION_DRIVER' => 'database',
    'SESSION_CONNECTION' => '',
    'SESSION_COOKIE' => 'gang_demo_session',
    'SESSION_DOMAIN' => 'null',
    'SESSION_SECURE_COOKIE' => 'false',
    'CACHE_STORE' => 'database',
    'DB_CACHE_CONNECTION' => '',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'log',
    'LOG_CHANNEL' => 'single',
    'FILESYSTEM_DISK' => 'local',
    'PAYMENT_GATEWAY' => 'demo',
    'PAYMENT_DEMO_ENABLED' => 'true',
    'STRIPE_SECRET_KEY' => '',
    'STRIPE_SECRET' => '',
    'STRIPE_WEBHOOK_SECRET' => '',
    'GEMINI_API_KEY' => '',
    'TEXTBEE_API_KEY' => '',
    'TEXTBEE_DEVICE_ID' => '',
];

$output = static function (string $type, string $buffer): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
};

try {
    $sqlite = new Process([...$php, '-r', 'exit(extension_loaded("pdo_sqlite") ? 0 : 1);'], $root);
    if ($sqlite->run() !== 0) {
        throw new RuntimeException('Enable the PHP pdo_sqlite extension to start the demo.');
    }

    (new Process([...$php, 'artisan', 'migrate', '--no-interaction'], $root, $environment))->mustRun($output);
    (new Process([...$php, 'artisan', 'db:seed', '--class=DemoDataSeeder', '--no-interaction'], $root, $environment))->mustRun($output);

    if (in_array('--seed-only', $argv, true)) {
        exit(0);
    }

    fwrite(STDOUT, "\nGANG demo: $url\n");
    (new Process([...$php, '-S', '127.0.0.1:8001', '-t', $public, __FILE__], $root, $environment, null, null))
        ->mustRun($output);
} catch (Exception $error) {
    fwrite(STDERR, "Demo setup failed: " . $error->getMessage() . PHP_EOL);
    exit(1);
}

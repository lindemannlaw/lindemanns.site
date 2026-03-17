<?php
// EINMALIG AUSFÜHREN – danach sofort löschen!
if (($_GET['token'] ?? '') !== 'tnd-migrate-2026') {
    die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>\n";

$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

$kernel->call('storage:link');
echo $kernel->output();

$kernel->call('view:clear');
echo $kernel->output();

$kernel->call('cache:clear');
echo $kernel->output();

// Check symlink status
$link = __DIR__ . '/storage';
if (is_link($link)) {
    echo "storage symlink OK → " . readlink($link) . "\n";
} elseif (is_dir($link)) {
    echo "WARNUNG: public/storage ist ein echter Ordner, kein Symlink!\n";
} else {
    echo "WARNUNG: public/storage existiert nicht!\n";
}

echo "\nFertig. Bitte diese Datei jetzt löschen!\n</pre>";

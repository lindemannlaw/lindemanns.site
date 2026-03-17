<?php
// EINMALIG AUSFÜHREN – danach sofort löschen!
if (($_GET['token'] ?? '') !== 'tnd-migrate-2026') {
    die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "<pre>\n";

$publicStorage = __DIR__ . '/storage';
$target = __DIR__ . '/../storage/app/public';

// Fix: replace real folder with proper symlink
if (is_dir($publicStorage) && !is_link($publicStorage)) {
    echo "Entferne echten Ordner public/storage...\n";

    // Move any files to storage/app/public first (safety)
    $files = array_diff(scandir($publicStorage), ['.', '..']);
    if (!empty($files)) {
        echo "Gefundene Dateien in public/storage: " . implode(', ', $files) . "\n";
        foreach ($files as $file) {
            $src = $publicStorage . '/' . $file;
            $dst = $target . '/' . $file;
            if (!file_exists($dst)) {
                rename($src, $dst);
                echo "Verschoben: $file\n";
            }
        }
    }

    // Remove the real folder
    rmdir($publicStorage);
    echo "Echter Ordner entfernt.\n";
}

// Create symlink
if (!file_exists($publicStorage)) {
    symlink($target, $publicStorage);
    echo "Symlink erstellt: public/storage → storage/app/public\n";
}

// Verify
if (is_link($publicStorage)) {
    echo "✓ Symlink OK → " . readlink($publicStorage) . "\n";
} else {
    echo "✗ Symlink konnte nicht erstellt werden!\n";
}

// Run artisan commands
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('view:clear');
echo $kernel->output();

$kernel->call('cache:clear');
echo $kernel->output();

echo "\nFertig. Bitte diese Datei jetzt löschen!\n</pre>";

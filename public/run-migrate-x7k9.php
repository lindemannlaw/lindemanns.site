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

function rrmdir(string $dir): void {
    foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
        $path = "$dir/$item";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

if (is_dir($publicStorage) && !is_link($publicStorage)) {
    echo "Entferne echten Ordner public/storage (rekursiv)...\n";
    rrmdir($publicStorage);
    echo "Ordner entfernt.\n";
}

if (!file_exists($publicStorage) && !is_link($publicStorage)) {
    symlink($target, $publicStorage);
}

if (is_link($publicStorage)) {
    echo "✓ Symlink OK → " . readlink($publicStorage) . "\n";
} else {
    echo "✗ Symlink konnte nicht erstellt werden!\n";
}

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('cache:clear');
echo $kernel->output();

echo "\nFertig. Bitte diese Datei jetzt löschen!\n</pre>";

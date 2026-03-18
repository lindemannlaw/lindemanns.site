<?php
if (($_GET['token'] ?? '') !== 'tnd-pd-2026') {
    http_response_code(403);
    die('Forbidden');
}

// ── Reset PHP Opcache FIRST (otherwise old compiled PHP keeps running) ──
echo "<pre>\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ Opcache reset\n";
} else {
    echo "– Opcache not available\n";
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

$kernel->call('storage:link', ['--force' => true]);
echo $kernel->output();

$kernel->call('view:clear');
echo $kernel->output();

$kernel->call('cache:clear');
echo $kernel->output();

$kernel->call('config:clear');
echo $kernel->output();

$kernel->call('route:clear');
echo $kernel->output();

echo "Done.\n";

// ── Optional: diagnose a project's description_blocks ──
// Usage: ?token=tnd-pd-2026&diagnose=PROJECT_ID
$diagnoseId = $_GET['diagnose'] ?? null;
if ($diagnoseId) {
    echo "\n=== DIAGNOSTIC for project #{$diagnoseId} ===\n";

    $rawJson = \Illuminate\Support\Facades\DB::table('projects')
        ->where('id', $diagnoseId)
        ->value('description_blocks');

    if ($rawJson === null) {
        echo "description_blocks column is NULL (no data or column missing)\n";
    } else {
        $decoded = json_decode($rawJson, true);
        if ($decoded === null) {
            echo "JSON decode FAILED. Raw (first 500 chars):\n";
            echo mb_substr($rawJson, 0, 500) . "\n";
        } else {
            foreach (['en', 'de'] as $locale) {
                $blocks = $decoded[$locale] ?? null;
                if ($blocks === null) {
                    echo "  [{$locale}] NOT PRESENT in JSON\n";
                    continue;
                }
                echo "  [{$locale}] " . count($blocks) . " blocks:\n";
                foreach ($blocks as $bi => $block) {
                    $type = $block['type'] ?? '???';
                    echo "    block[{$bi}] type={$type}";
                    if (isset($block['items'])) {
                        foreach ($block['items'] as $ii => $item) {
                            $cs = $item['col_span'] ?? '-';
                            $cst = $item['col_start'] ?? '-';
                            echo " | item[{$ii}] col_span={$cs} col_start={$cst}";
                        }
                    }
                    echo "\n";
                }
            }
        }
    }

    // Show file modification time of key PHP files to verify deploy
    $files = [
        'Controller' => __DIR__ . '/../app/Http/Controllers/Admin/Portfolio/ProjectController.php',
        'Model' => __DIR__ . '/../app/Models/Project.php',
    ];
    echo "\n=== FILE TIMESTAMPS (deploy check) ===\n";
    foreach ($files as $label => $path) {
        if (file_exists($path)) {
            echo "  {$label}: " . date('Y-m-d H:i:s', filemtime($path)) . "\n";
        } else {
            echo "  {$label}: FILE NOT FOUND\n";
        }
    }
}

echo "</pre>";

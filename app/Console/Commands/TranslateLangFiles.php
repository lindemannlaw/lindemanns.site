<?php

namespace App\Console\Commands;

use App\Services\DeepLTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateLangFiles extends Command
{
    protected $signature = 'app:translate-lang-files
                            {--locale=all : Target locale or "all" for all non-EN/DE locales}
                            {--source=en : Source locale to translate from}
                            {--dry-run : Show what would be translated without writing}';

    protected $description = 'Translate lang/*.php files via DeepL API';

    // DeepL uses specific language codes
    private const DEEPL_LANG_MAP = [
        'en' => 'EN',
        'de' => 'DE',
        'fr' => 'FR',
        'ru' => 'RU',
        'el' => 'EL',
        'pl' => 'PL',
        'ar' => 'AR',
        'zh' => 'ZH',
    ];

    public function handle(DeepLTranslationService $deepl): int
    {
        if (!$deepl->isConfigured()) {
            $this->error('DeepL API key is not configured. Set DEEPL_API_KEY in .env');
            return self::FAILURE;
        }

        $sourceLang = $this->option('source');
        $sourcePath = lang_path($sourceLang);

        if (!File::isDirectory($sourcePath)) {
            $this->error("Source language directory not found: {$sourcePath}");
            return self::FAILURE;
        }

        $targetLocales = $this->resolveTargetLocales($sourceLang);

        if (empty($targetLocales)) {
            $this->warn('No target locales to translate.');
            return self::SUCCESS;
        }

        $files = File::glob("{$sourcePath}/*.php");
        $this->info("Translating " . count($files) . " files from [{$sourceLang}] to [" . implode(', ', $targetLocales) . "]");

        foreach ($targetLocales as $locale) {
            $deeplTarget = self::DEEPL_LANG_MAP[$locale] ?? strtoupper($locale);
            $this->newLine();
            $this->info("=== Translating to: {$locale} ({$deeplTarget}) ===");

            foreach ($files as $file) {
                $filename = basename($file);
                $this->translateFile($deepl, $file, $locale, $deeplTarget, $filename);
            }
        }

        $this->newLine();
        $this->info('Done!');
        return self::SUCCESS;
    }

    private function resolveTargetLocales(string $sourceLang): array
    {
        $optionLocale = $this->option('locale');

        if ($optionLocale !== 'all') {
            return [$optionLocale];
        }

        // All locale dirs except source and 'de' (which has manual translations)
        $dirs = array_map('basename', File::directories(lang_path()));
        return array_values(array_filter($dirs, fn ($d) => !in_array($d, [$sourceLang, 'de'])));
    }

    private function translateFile(DeepLTranslationService $deepl, string $sourceFile, string $locale, string $deeplTarget, string $filename): void
    {
        $sourceData = include $sourceFile;

        if (!is_array($sourceData)) {
            $this->warn("  Skipping {$filename}: not a valid array");
            return;
        }

        $targetDir = lang_path($locale);
        File::ensureDirectoryExists($targetDir);
        $targetFile = "{$targetDir}/{$filename}";

        // Flatten nested arrays for translation
        $flat = $this->flattenArray($sourceData);
        $keys = array_keys($flat);
        $values = array_values($flat);

        $this->line("  {$filename}: " . count($values) . " strings");

        if ($this->option('dry-run')) {
            return;
        }

        // Batch translate (DeepL handles up to 50 at a time)
        $translated = [];
        $chunks = array_chunk($values, 50, true);
        $chunkKeys = array_chunk($keys, 50, true);

        foreach ($chunks as $i => $chunk) {
            $items = array_map(fn ($text) => ['text' => (string) $text, 'isHtml' => false], $chunk);
            $results = $deepl->translate($items, strtoupper($this->option('source')), $deeplTarget);
            $translated = array_merge($translated, $results);

            // Small delay between batches to avoid rate limits
            if (count($chunks) > 1 && $i < count($chunks) - 1) {
                usleep(300000); // 300ms
            }
        }

        // Reconstruct nested array
        $translatedFlat = array_combine($keys, $translated);
        $translatedData = $this->unflattenArray($translatedFlat);

        // Write PHP file
        $content = "<?php\n\nreturn " . $this->arrayToPhpString($translatedData) . ";\n";
        File::put($targetFile, $content);

        $this->info("  ✓ {$filename} → {$locale}");
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : (string) $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private function unflattenArray(array $flat): array
    {
        $result = [];
        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            $current = &$result;
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    if (!isset($current[$part]) || !is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
            unset($current);
        }
        return $result;
    }

    private function arrayToPhpString(array $array, int $indent = 1): string
    {
        $pad = str_repeat('    ', $indent);
        $padOuter = str_repeat('    ', $indent - 1);
        $lines = ["["];

        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? $key : "'" . addcslashes($key, "'\\") . "'";

            if (is_array($value)) {
                $lines[] = "{$pad}{$keyStr} => " . $this->arrayToPhpString($value, $indent + 1) . ",";
            } else {
                $valueStr = "'" . addcslashes((string) $value, "'\\") . "'";
                $lines[] = "{$pad}{$keyStr} => {$valueStr},";
            }
        }

        $lines[] = "{$padOuter}]";
        return implode("\n", $lines);
    }
}

<?php
declare(strict_types=1);

$directories = ['system', 'modules'];
$errors = 0;
$missing_strict = 0;

function scan_dir($dir, &$errors, &$missing_strict) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scan_dir($path, $errors, $missing_strict);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            // Lint check
            $output = [];
            $return_var = 0;
            exec("php -l " . escapeshellarg($path), $output, $return_var);
            if ($return_var !== 0) {
                echo "LINT FAIL: $path\n";
                echo implode("\n", $output) . "\n";
                $errors++;
            }

            // Strict types check
            $content = file_get_contents($path);
            if (strpos($content, 'declare(strict_types=1)') === false) {
                // Only report classes
                if (strpos($content, 'class ') !== false || strpos($content, 'interface ') !== false || strpos($content, 'trait ') !== false) {
                    echo "MISSING STRICT_TYPES: $path\n";
                    $missing_strict++;
                }
            }
        }
    }
}

foreach ($directories as $dir) {
    scan_dir($dir, $errors, $missing_strict);
}

echo "\nValidation Results:\n";
echo "Lint errors: $errors\n";
echo "Classes missing strict_types: $missing_strict\n";

exit($errors > 0 ? 1 : 0);

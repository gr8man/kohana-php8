#!/usr/bin/env php
<?php
/**
 * Script to add declare(strict_types=1) to PHP files
 */

$dirs = $argv;
array_shift($dirs);

if (empty($dirs)) {
    echo "Usage: php add_strict_types.php <directory1> <directory2> ...\n";
    exit(1);
}

$count = 0;
$errors = [];

function processFile($path) {
    global $count, $errors;
    
    if (!is_file($path) || !preg_match('/\.php$/', $path)) {
        return;
    }
    
    $content = file_get_contents($path);
    if ($content === false) {
        $errors[] = "Cannot read: $path";
        return;
    }
    
    // Check if already has strict_types
    if (preg_match('/<\?php\s+declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $content)) {
        return;
    }
    
    // Check if has <?php at the beginning
    if (preg_match('/^<\?php/', $content)) {
        // Add strict_types after <?php
        $newContent = preg_replace(
            '/^<\?php\b/',
            "<?php\n\ndeclare(strict_types=1);",
            $content
        );
    } else {
        // Prepend <?php and strict_types
        $newContent = "<?php\n\ndeclare(strict_types=1);\n\n" . $content;
    }
    
    if ($newContent === $content) {
        return;
    }
    
    if (file_put_contents($path, $newContent) === false) {
        $errors[] = "Cannot write: $path";
        return;
    }
    
    $count++;
}

function processDirectory($dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.php$/', $file->getPathname())) {
            processFile($file->getPathname());
        }
    }
}

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        processDirectory($dir);
    } elseif (is_file($dir)) {
        processFile($dir);
    }
}

echo "Added strict_types to $count files.\n";
if (!empty($errors)) {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

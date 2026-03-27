<?php
$dir = 'system/tests/kohana';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    $changed = false;
    
    // Add #[AllowDynamicProperties]
    if (strpos($content, 'class ') !== false && strpos($content, '#[AllowDynamicProperties]') === false) {
        $content = preg_replace('/(class\s+Kohana_\w+)/', "#[AllowDynamicProperties]\n$1", $content);
        $changed = true;
    }
    
    // Replace @expectedException
    if (preg_match_all('/\* @expectedException\s+([\\\\a-zA-Z0-9_]+)/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $exception = $match[1];
            // Find the function definition following this docblock
            $pattern = '/' . preg_quote($match[0], '/') . '.*?\s+public\s+function\s+(\w+)\s*\(/s';
            if (preg_match($pattern, $content, $funcMatch)) {
                $funcName = $funcMatch[1];
                // Insert $this->expectException inside the function
                $content = preg_replace('/(function\s+' . preg_quote($funcName, '/') . '\s*\([^)]*\)\s*\{)/', "$1\n\t\t\$this->expectException('$exception');", $content);
                // Remove the annotation
                $content = str_replace($match[0], '*', $content);
                $changed = true;
            }
        }
    }

    // Replace @expectedExceptionMessage
    if (preg_match_all('/\* @expectedExceptionMessage\s+(.*)/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $message = trim($match[1]);
            // Remove the annotation
            $content = str_replace($match[0], '*', $content);
            // We'll assume it's already inside a function that has expectException
            // This is a bit risky but works for Kohana tests
            $content = preg_replace('/(\$this->expectException\(.*\);)/', "$1\n\t\t\$this->expectExceptionMessage('$message');", $content);
            $changed = true;
        }
    }
    
    if ($changed) {
        file_put_contents($file->getPathname(), $content);
        echo "Updated " . $file->getPathname() . "\n";
    }
}

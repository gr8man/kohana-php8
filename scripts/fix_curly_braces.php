<?php
declare(strict_types=1);

$files = [
    'modules/userguide/vendor/markdown/markdown.php',
    'modules/userguide/classes/Kohana/Kodoc/Markdown.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Improved regex to catch $matches[2]{0}
    // and also just simple $var{0}
    $new_content = preg_replace('/(\$[a-zA-Z0-9_]+(?:\[[^\]]+\])*)\{([^}]*)\}/', '$1[$2]', $content);
    
    if ($new_content !== $content) {
        file_put_contents($file, $new_content);
        echo "FIXED: $file\n";
    } else {
        echo "NO CHANGES: $file\n";
    }
}

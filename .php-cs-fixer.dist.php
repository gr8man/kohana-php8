<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/system')
    ->in(__DIR__ . '/modules')
    ->in(__DIR__ . '/application')
    ->exclude('vendor')
    ->exclude('cache')
    ->notPath('tests/test_data/views/test.css.php')
    ->notPath('views/minion/error/validation.php')
    ->notPath('views/minion/help/list.php')
    ->notPath('views/minion/help/task.php')
    ->notPath('views/userguide/api/class.php')
    ->notPath('views/userguide/api/menu.php')
    ->notPath('views/userguide/api/method.php')
    ->notPath('views/userguide/api/tags.php')
    ->notPath('views/userguide/api/toc.php')
    ->notPath('views/userguide/error.php')
    ->notPath('views/userguide/examples/hello_world_error.php')
    ->notPath('views/userguide/index.php')
    ->notPath('views/userguide/menu.php')
    ->notPath('views/userguide/template.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        // Keep Kohana style long arrays to minimize diff noise
        'array_syntax' => ['syntax' => 'long'],
        // Indentation rules
        'indentation_type' => true,
        'line_ending' => true,
    ])
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setFinder($finder);

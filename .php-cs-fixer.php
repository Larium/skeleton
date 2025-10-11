<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRules([
        '@PSR12' => true,
        'yoda_style' => false,
        'ordered_imports' => ['sort_algorithm' => 'length', 'imports_order' => ['const', 'class', 'function']],
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'blank_line_between_import_groups' => true,
    ])->setFinder($finder)
;

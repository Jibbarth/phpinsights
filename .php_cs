<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'comment_to_phpdoc' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'increment_style' => null,
        'multiline_whitespace_before_semicolons' => null,
        'native_function_invocation' => null,
        'no_superfluous_phpdoc_tags' => true,
        'not_operator_with_successor_space' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'single_line_comment_style' => null,
        'yoda_style' => null,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__)
    );

<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
        '.git',
        '.idea',
        '.vscode',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,

        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'nullable_type_declaration_for_default_null_value' => true,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'all',
        ],

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'no_unused_imports' => true,

        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'concat_space' => ['spacing' => 'one'],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'single_line_empty_body' => true,

        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'const' => 'none',
            ],
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'return_type_declaration' => ['space_before' => 'none'],

        'single_quote' => false,
        'escape_implicit_backslashes' => false,

        'native_constant_invocation' => true,
        'modernize_strpos' => true,
        'ternary_to_null_coalescing' => true,

        'increment_style' => false,
        'logical_operators' => false,
        'php_unit_strict' => false,
    ]);

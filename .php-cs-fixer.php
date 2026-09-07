<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfig;

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
    ->setParallelConfig(new ParallelConfig(8))
    ->setRules([
        '@PSR12' => true,
        '@PHP8x1Migration' => true,
        '@PHP8x1Migration:risky' => true,

        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'nullable_type_declaration_for_default_null_value' => true,
        'native_function_invocation' => false,

        'ordered_imports' => [
            'sort_algorithm' => 'alpha'
        ],
        'fully_qualified_strict_types' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'header_comment' => [
            'comment_type' => 'comment',
            'header' => <<<TEXT
__        __    _                                    __  __  ____ 
\ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |    
  \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___ 
   \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|

WatermossMC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

@author WatermossMC Team
@link https://github.com/watermossmc/WatermossMC
TEXT,
            'location' => 'after_open',
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
                'trait_import' => 'none',
            ],
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'return_type_declaration' => ['space_before' => 'none'],

        'single_quote' => false,
        'string_implicit_backslashes' => false,

        'native_constant_invocation' => false,
        'modernize_strpos' => true,
        'ternary_to_null_coalescing' => true,

        'increment_style' => false,
        'logical_operators' => false,
        'php_unit_strict' => false,
    ]);

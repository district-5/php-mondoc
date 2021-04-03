<?php
$header = [
    'District5 - Mondoc',
    '',
    '@copyright District5',
    '',
    '@author District5',
    '@link https://www.district5.co.uk',
    '',
    '@license This software and associated documentation (the "Software") may not be',
    'used, copied, modified, distributed, published or licensed to any 3rd party',
    'without the written permission of District5 or its author.',
    '',
    'The above copyright notice and this permission notice shall be included in',
    'all licensed copies of the Software.'
];

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

$cs = new PhpCsFixer\Config();

return $cs->setRiskyAllowed(
    false
)->setRules(
    [
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'final_class' => false,
        'new_with_braces' => true,
        'php_unit_test_class_requires_covers' => false,
        'header_comment' => [
            'header' => implode(PHP_EOL, $header),
            'comment_type' => 'PHPDoc',
        ],
        'no_spaces_after_function_name' => true,
        'no_unused_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'no_empty_phpdoc' => false,
        'lowercase_static_reference' => true,
        'phpdoc_no_package' => false,
        'no_superfluous_phpdoc_tags' => false,
        'trailing_comma_in_multiline_array' => false
    ]
)->setFinder(
    $finder
)->setIndent(
    str_pad('', 4)
)->setUsingCache(
    false
);

<?php declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->files()
    ->name('*.php')
    ->in([
        __DIR__ . '/src',
    ]);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        'psr_autoloading' => true,
        'void_return' => true,
        'no_empty_statement' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'no_extra_blank_lines' => true,
        'no_whitespace_in_blank_line' => true,
        'no_blank_lines_after_class_opening' => true,
        'semicolon_after_instruction' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'no_empty_phpdoc' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'ternary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
    ]);

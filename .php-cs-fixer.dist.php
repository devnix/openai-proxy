<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    // ->in('tests')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@Symfony' => true,
        'class_attributes_separation' => ['elements' => ['const' => 'one', 'method' => 'one', 'property' => 'one']],
        // 'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'php_unit_method_casing' => false,
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'var',
                'return',
                'throws',
            ],
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'native_function_invocation' => true,
        'multiline_promoted_properties' => true,
    ])
    ->setFinder($finder)
;

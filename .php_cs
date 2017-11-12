<?php

if (!file_exists(__DIR__ . '/src')) {
    exit(0);
}

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'protected_to_private' => false,
        // Modified from Symfony preset to reduce changes in existing code
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => null,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([__DIR__ . '/src', __DIR__ . '/tests'])
            ->append([__FILE__])
    );

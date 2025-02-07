<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])

    // add a single rule
    ->withRules([
        NoUnusedImportsFixer::class,
        BracesPositionFixer::class,
    ])

    // add sets - group of rules
    ->withPreparedSets(
        arrays: true,
        comments: true,
        docblocks: true,
        spaces: true,
        namespaces: true,
    )
    ->withPhpCsFixerSets(symfony: true)
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
        MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
        ],
    ]);
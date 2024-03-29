<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EncoreConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ExtensionCollection::class)) {
            return;
        }

        $collectionDefinition = $container->findDefinition(ExtensionCollection::class);

        /** @var class-string<EncoreExtensionInterface> $id */
        foreach ($container->findTaggedServiceIds('huh.encore.extension') as $id => $tags) {
            $collectionDefinition->addMethodCall('addExtension', [new Reference($id)]);
        }
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EncoreConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
//        $config = $container->getParameter('huh_encore');

        if (!$container->has(ExtensionCollection::class)) {
            return;
        }

        $collectionDefinition = $container->findDefinition(ExtensionCollection::class);

        /** @var class-string<EncoreExtensionInterface> $id */
        foreach ($container->findTaggedServiceIds('huh.encore.extension') as $id => $tags) {
            $collectionDefinition->addMethodCall('addExtension', [new Reference($id)]);

//            /** @var EncoreEntry $entry */
//            foreach ($id::getEntries() as $entry) {
//                $path = $entry->getPath();
//                if (!str_starts_with($path, '@')) {
//                    $path = '@'.substr($id::getBundle(), strrpos($id::getBundle(), '\\')+1).DIRECTORY_SEPARATOR.$path;
//                }
//
//                $config['js_entries'][] = [
//                    'name' => $entry->getName(),
//                    'file' => $path,
//                    'requires_css' => $entry->getRequiresCss(),
//                    'head' => $entry->getIsHeadScript(),
//                ];
//                $globalKeys = $entry->getReplaceGlobelKeys();
//                $config['unset_global_keys']['js'] = array_merge($config['unset_global_keys']['js'], $globalKeys['TL_JAVASCRIPT'] ?? []);
//                $config['unset_global_keys']['css'] = array_merge($config['unset_global_keys']['css'], $globalKeys['TL_CSS'] ?? []);
//                $config['unset_global_keys']['css'] = array_merge($config['unset_global_keys']['css'], $globalKeys['TL_USER_CSS'] ?? []);
//                $config['unset_global_keys']['jquery'] = array_merge($config['unset_global_keys']['jquery'], $globalKeys['TL_JQUERY'] ?? []);
//            }
        }

//        $container->setParameter('huh_encore', $config);
    }
}

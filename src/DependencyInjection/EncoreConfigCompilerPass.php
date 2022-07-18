<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EncoreConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('huh_encore');

        /** @var class-string<EncoreExtensionInterface> $id */
        foreach ($container->findTaggedServiceIds('huh.encore.extension') as $id => $tags) {
            /** @var EncoreEntry $entry */
            foreach ($id::getEntries() as $entry) {
                $config['js_entries'][] = [
                    'name' => $entry->getName(),
                    'file' => $entry->getPath(),
                    'requires_css' => $entry->getRequiresCss(),
                    'head' => $entry->getIsHeadScript(),
                ];
                $globalKeys = $entry->getReplaceGlobelKeys();
                $config['unset_global_keys']['js'] = array_merge($config['unset_global_keys']['js'], $globalKeys['TL_JAVASCRIPT'] ?? []);
                $config['unset_global_keys']['css'] = array_merge($config['unset_global_keys']['css'], $globalKeys['TL_CSS'] ?? []);
                $config['unset_global_keys']['css'] = array_merge($config['unset_global_keys']['css'], $globalKeys['TL_USER_CSS'] ?? []);
                $config['unset_global_keys']['jquery'] = array_merge($config['unset_global_keys']['jquery'], $globalKeys['TL_JQUERY'] ?? []);
            }
        }
    }
}

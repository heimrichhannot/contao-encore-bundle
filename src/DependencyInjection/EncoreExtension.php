<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EncoreExtension extends Extension implements PrependExtensionInterface
{
    private $entrypointsJsons = [];

    private $encoreCacheEnabled = false;

    public function getAlias()
    {
        return 'huh_encore';
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Load current configuration of the webpack encore bundle
        $configs = $container->getExtensionConfig('webpack_encore');
        $processedConfigs = $this->processConfiguration(new \Symfony\WebpackEncoreBundle\DependencyInjection\Configuration(), $configs);

        $this->encoreCacheEnabled = $processedConfigs['cache'];
        if (false !== $processedConfigs['output_path']) {
            $this->entrypointsJsons[] = $processedConfigs['output_path'].'/entrypoints.json';
        } else {
            // TODO: multiple builds are not supported yet
            throw new \Exception('Multiple encore builds are currently not supported by the Contao Encore Bundle');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $legacyConfig = $processedConfig['encore'];
        unset($processedConfig['encore']);
        $processedConfig = array_merge($legacyConfig, $processedConfig);

        $processedConfig['entrypoints_jsons'] = $this->entrypointsJsons;
        $processedConfig['encore_cache_enabled'] = $this->encoreCacheEnabled;

        $container->setParameter('huh_encore', $processedConfig);

        // Deprecated:
        $container->setParameter('huh.encore', ['encore' => $processedConfig]);
        $processedConfig['entrypointsJsons'] = $this->entrypointsJsons;
        $processedConfig['encoreCacheEnabled'] = $this->encoreCacheEnabled;

    }
}

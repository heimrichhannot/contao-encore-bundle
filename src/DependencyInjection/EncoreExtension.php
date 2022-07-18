<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use HeimrichHannot\EncoreBundle\Exception\FeatureNotSupportedException;
use HeimrichHannot\EncoreBundle\Helper\ArrayHelper;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EncoreExtension extends Extension implements PrependExtensionInterface
{
    private $entrypointsJsons = [];

    private $encoreCacheEnabled = false;

    private $outputPath = '';

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
            $this->outputPath = $processedConfigs['output_path'];
        } else {
            // TODO: multiple builds are not supported yet
            throw new FeatureNotSupportedException('Multiple encore builds are currently not supported by the Contao Encore Bundle');
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
        $processedConfig = $this->mergeLegacyConfig($processedConfig, $legacyConfig);

        $processedConfig['entrypoints_jsons'] = $this->entrypointsJsons;
        $processedConfig['encore_cache_enabled'] = $this->encoreCacheEnabled;
        $processedConfig['outputPath'] = $this->outputPath;

        $container->setParameter('huh_encore', $processedConfig);

        $container->registerForAutoconfiguration(EncoreExtensionInterface::class)
            ->addTag('huh.encore.extension');

        // Deprecated:
        $container->setParameter('huh.encore', ['encore' => $processedConfig]);
        $processedConfig['entrypointsJsons'] = $this->entrypointsJsons;
        $processedConfig['encoreCacheEnabled'] = $this->encoreCacheEnabled;
    }

    /**
     * Merge legacy bundle config into bundle config.
     *
     * @todo Remove with version 2.0
     */
    public function mergeLegacyConfig(array $config, array $legacyConfig)
    {
        if (empty($legacyConfig)) {
            return $config;
        }
        if (!empty($legacyConfig['entries'])) {
            $legacyConfig['js_entries'] = $legacyConfig['entries'];
            unset($legacyConfig['entries']);
            foreach ($legacyConfig['js_entries'] as &$entry) {
                if (!isset($entry['requiresCss'])) {
                    continue;
                }
                $entry['requires_css'] = $entry['requiresCss'];
                unset($entry['requiresCss']);
            }
        }

        $mergedConfig = $config;
        if (isset($legacyConfig['js_entries'])) {
            $mergedConfig['js_entries'] = ArrayHelper::arrayUniqueMultidimensional(array_merge($config['js_entries'], $legacyConfig['js_entries']), 'name');
        }
        $mergedConfig['templates']['imports'] = ArrayHelper::arrayUniqueMultidimensional(array_merge($config['templates']['imports'], $legacyConfig['templates']['imports']), 'name');

        $mergedConfig['unset_global_keys']['js'] = array_unique(array_merge($config['unset_global_keys']['js'], $legacyConfig['legacy']['js']));
        $mergedConfig['unset_global_keys']['jquery'] = array_unique(array_merge($config['unset_global_keys']['jquery'], $legacyConfig['legacy']['jquery']));
        $mergedConfig['unset_global_keys']['css'] = array_unique(array_merge($config['unset_global_keys']['css'], $legacyConfig['legacy']['css']));

        return $mergedConfig;
    }
}

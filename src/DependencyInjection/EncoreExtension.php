<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

        if ($processedConfigs['output_path'] !== false) {
            $this->entrypointsJsons[] = $processedConfigs['output_path'] . '/entrypoints.json';
        } else {
            // TODO: multiple builds are not supported yet
            throw new \Exception('Multiple encore are currently not supported by the Contao Encore Bundle');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $processedConfig['encore']['entrypointsJsons'] = $this->entrypointsJsons;

        $container->setParameter('huh.encore', $processedConfig);
    }
}

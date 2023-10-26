<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use Composer\InstalledVersions;
use HeimrichHannot\EncoreBundle\Exception\FeatureNotSupportedException;
use HeimrichHannot\EncoreBundle\Helper\ArrayHelper;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Webmozart\PathUtil\Path;

class EncoreExtension extends Extension implements PrependExtensionInterface
{
    private $entrypointsJsons = [];

    private $encoreCacheEnabled = false;

    private $outputPath = '';

    public function getAlias(): string
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
        $outputPath = '';
        foreach ($configs as $config) {
            $outputPath = $config['output_path'] ?? $outputPath;
        }

        if (false !== $outputPath) {
            if (empty($outputPath)) {
                if (version_compare(InstalledVersions::getPrettyVersion('contao/core-bundle'), '4.12', '>=')) {
                    $outputPath = '%kernel.project_dir%/public/build';
                } else {
                    $outputPath = '%kernel.project_dir%/web/build';
                }
                if (\is_string($projectDir = $container->getParameter('kernel.project_dir'))) {
                    $publicPath = $this->getComposerPublicDir($projectDir);
                    if (null !== $publicPath) {
                        $outputPath = $publicPath.\DIRECTORY_SEPARATOR.'build';
                    }
                }
                $container->prependExtensionConfig('webpack_encore', [
                    'output_path' => $outputPath,
                ]);
            }
            $this->entrypointsJsons[] = $outputPath.'/entrypoints.json';
            $this->outputPath = $outputPath;
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

        $processedConfig['entrypoints_jsons'] = $this->entrypointsJsons;
        $processedConfig['encore_cache_enabled'] = $this->encoreCacheEnabled;
        $processedConfig['outputPath'] = $this->outputPath;

        $container->setParameter('huh_encore', $processedConfig);

        $container->registerForAutoconfiguration(EncoreExtensionInterface::class)
            ->addTag('huh.encore.extension');
    }

    /**
     * Copy from \Contao\CoreBundle\DependencyInjection\ContaoCoreExtension.
     */
    private function getComposerPublicDir(string $projectDir): ?string
    {
        $fs = new Filesystem();

        if (!$fs->exists($composerJsonFilePath = Path::join($projectDir, 'composer.json'))) {
            return null;
        }

        $composerConfig = json_decode(file_get_contents($composerJsonFilePath), true, 512, \JSON_THROW_ON_ERROR);

        if (null === ($publicDir = $composerConfig['extra']['public-dir'] ?? null)) {
            return null;
        }

        return Path::join($projectDir, $publicDir);
    }
}

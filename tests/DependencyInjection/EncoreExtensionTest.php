<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension;
use HeimrichHannot\EncoreBundle\Exception\FeatureNotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EncoreExtensionTest extends ContaoTestCase
{
    public function testGetAlias()
    {
        $extension = new EncoreExtension();
        $this->assertSame('huh_encore', $extension->getAlias());
    }

    /**
     * @dataProvider outputPathProvider
     */
    public function testPrepend(array $configs, string $projectDir, string $expectedOutputPath): void
    {
        $extension = new EncoreExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $projectDir);

        foreach ($configs as $config) {
            $container->prependExtensionConfig('webpack_encore', $config);
        }

        $extension->prepend($container);
        $extension->load([], $container);

        $this->assertSame($expectedOutputPath, $container->getExtensionConfig('webpack_encore')[0]['output_path']);
        $this->assertSame($expectedOutputPath, $container->getParameter('huh_encore')['outputPath']);
        $this->assertSame([$expectedOutputPath . '/entrypoints.json'], $container->getParameter('huh_encore')['entrypoints_jsons']);
    }

    public function outputPathProvider(): array
    {
        $fixtures = \dirname(__DIR__) . '/Fixtures/DependencyInjection';

        return [
            'default without composer file' => [[], $fixtures, '%kernel.project_dir%/public/build'],
            'default without public-dir setting' => [[], $fixtures . '/no_public_dir', '%kernel.project_dir%/public/build'],
            'explicit output path' => [[['output_path' => 'custom/assets']], $fixtures . '/public_dir_web', 'custom/assets'],
            'explicit web output path' => [[['output_path' => 'web/build']], $fixtures, 'web/build'],
            'custom web directory' => [[], $fixtures . '/public_dir_web', $fixtures . '/public_dir_web/web/build'],
            'custom public directory' => [[], $fixtures . '/public_dir_public', $fixtures . '/public_dir_public/public/build'],
        ];
    }

    public function testMultipleBuildsAreNotSupported(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig('webpack_encore', ['output_path' => false]);

        $this->expectException(FeatureNotSupportedException::class);

        (new EncoreExtension())->prepend($container);
    }
}

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
    public function testMergeLegacyConfig()
    {
        $extension = new EncoreExtension();
        $config = [
            'js_entries' => [
                ['name' => 'contao-vender-bundle', 'file' => 'index.js', 'requires_css' => true],
                ['name' => 'contao-extension-bundle', 'file' => 'extension.js'],
                ['name' => 'contao-head-scripts', 'file' => 'head.js', 'head' => true],
                ['name' => 'contao-double-extension', 'file' => 'double.js', 'requires_css' => true],
            ],
            'templates' => [
                'imports' => [
                    ['name' => 'default_css', 'default_css.html.twig'],
                    ['name' => 'default_js', 'default_js.html.twig'],
                ],
            ],
            'unset_global_keys' => [
                'js' => ['asset_js_1', 'asset_js_2'],
                'jquery' => [],
                'css' => ['asset_css_1', 'asset_css_2'],
            ],
            'unset_jquery' => false,
        ];
        $legacyConfig = [
            'entries' => [
                ['name' => 'contao-double-extension', 'file' => 'double.js', 'requiresCss' => true],
                ['name' => 'contao-another-extension', 'file' => 'another.js'],
            ],
            'templates' => [
                'imports' => [
                    ['name' => 'inline_css', 'inline_css.html.twig'],
                    ['name' => 'default_js', 'default_js.html.twig'],
                ],
            ],
            'legacy' => [
                'js' => ['asset_js_3', 'asset_js_2'],
                'jquery' => ['asset_jquery_1'],
                'css' => ['asset_css_4', 'asset_css_5'],
            ],
        ];

        $mergedConfig = $extension->mergeLegacyConfig($config, $legacyConfig);
        $this->assertCount(4, $mergedConfig);
        $this->assertCount(5, $mergedConfig['js_entries']);
        $this->assertCount(1, $mergedConfig['templates']);
        $this->assertCount(3, $mergedConfig['templates']['imports']);
        $this->assertCount(3, $mergedConfig['unset_global_keys']);
        $this->assertCount(3, $mergedConfig['unset_global_keys']['js']);
        $this->assertCount(1, $mergedConfig['unset_global_keys']['jquery']);
        $this->assertCount(4, $mergedConfig['unset_global_keys']['css']);

        $this->assertArrayNotHasKey('legacy', $mergedConfig);
        $this->assertArrayNotHasKey('entries', $mergedConfig);

        $mergedConfig = $extension->mergeLegacyConfig($config, []);
        $this->assertCount(4, $mergedConfig);
        $this->assertCount(4, $mergedConfig['js_entries']);
        $this->assertCount(1, $mergedConfig['templates']);
        $this->assertCount(2, $mergedConfig['templates']['imports']);
        $this->assertCount(3, $mergedConfig['unset_global_keys']);
        $this->assertCount(2, $mergedConfig['unset_global_keys']['js']);
        $this->assertCount(0, $mergedConfig['unset_global_keys']['jquery']);
        $this->assertCount(2, $mergedConfig['unset_global_keys']['css']);

        $mergedConfig = $extension->mergeLegacyConfig($config, [
            'entries' => [],
            'templates' => ['imports' => []],
            'legacy' => [
                'js' => [],
                'jquery' => [],
                'css' => [],
            ],
        ]);
        $mergedConfig = $extension->mergeLegacyConfig($config, []);
    }

    public function testGetAlias()
    {
        $extension = new EncoreExtension();
        $this->assertSame('huh_encore', $extension->getAlias());
    }

    public function testPrepend()
    {
        //
        // Preconfigured public path
        //

        $extension = new EncoreExtension();
        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([
            ['output_path' => 'web/build'],
        ]);
        $container->expects($this->never())->method('getParameter');
        $extension->prepend($container);

        //
        // test without composer public_path setting
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '%kernel.project_dir%');
            }));
        $extension->prepend($container);

        //
        // test with public_path = web
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('getParameter')->willReturn(__DIR__.'/../Fixtures/DependencyInjection/public_dir_web');
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '/web/build');
            }));
        $extension->prepend($container);

        //
        // test with public_path = public
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('getParameter')->willReturn(__DIR__.'/../Fixtures/DependencyInjection/public_dir_public');
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '/public/build');
            }));
        $extension->prepend($container);

        //
        // With multiple builds
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([
            ['output_path' => false],
        ]);

        $exception = false;
        try {
            $extension->prepend($container);
        } catch (FeatureNotSupportedException $e) {
            $exception = true;
        }

        $this->assertTrue($exception);
    }
}

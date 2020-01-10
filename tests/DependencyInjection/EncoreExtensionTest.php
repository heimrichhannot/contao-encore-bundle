<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\DependencyInjection;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension;

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
                'css' => ['asset_css_1','asset_css_2'],
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
                'css' => ['asset_css_4','asset_css_5'],
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
}
<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\Asset;


use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class PageEntrypointsTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function mockPageEntrypointsObject(array $bundleConfig = [])
    {
        $entrypointsJsonLookup = $this->createMock(EntrypointsJsonLookup::class);
        $entrypointsJsonLookup->method('mergeEntries')->willReturnCallback(function (array $entrypointsJsons, array $entries, LayoutModel $layout = null) {
           foreach ($entrypointsJsons as $name => $entrypoint) {
               $entry = ['name' => $name];
               if (isset($entrypoint['css'])) {
                   $entry['requiresCss'] = '1';
               }
               $entries[] = $entry;
           }
           return $entries;
        });

        $container = $this->mockContainer();
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findParentsRecursively')->willReturnCallback(function (string $parentProperty, string $table, Model $instance) {
            switch ($instance->id) {
                default:
                    return [];
            }
        });

        $container->set('huh.utils.model', $modelUtil);
        $frontendAsset = new FrontendAsset();
        $frontendAsset->addActiveEntrypoint('contao-slick-bundle');
        return new PageEntrypoints($bundleConfig, $entrypointsJsonLookup, $container, $frontendAsset);
    }

    public function entryPointProvider()
    {
        return  [
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                [],
                null
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => ''],
                null
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => 123],
                null
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => []],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => [[],[]]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => [['a'],['b']]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => [['name' => 'contao-encore-bundle'],['name' => 'b']]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['entries' => [['name' => 'contao-encore-bundle'],['name' => 'contao-slick-bundle']]],
                null,
                1
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([['entry' => 'contao-encore-bundle']])]),
                ['entries' => [['name' => 'contao-encore-bundle'],['name' => 'contao-slick-bundle']]],
                null,
                2
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([['entry' => 'contao-encore-bundle']])]),
                ['entries' => [
                    ['name' => 'contao-encore-bundle', 'head' => '1'],
                    ['name' => 'contao-slick-bundle', 'requiresCss' => '1'],
                    ['name' => 'contao-a-bundle'],
                    ['name' => 'contao-b-bundle'],
                ]],
                null,
                3
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'bootstrap'],
                ])]),
                [
                    'entries' => [
                        ['name' => 'contao-encore-bundle', 'head' => '1'],
                        ['name' => 'contao-slick-bundle', 'requiresCss' => '1'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ],
                    'entrypointsJsons' => [
                        'bootstrap' => [
                            "js" => ['boostrap.js'],
                            "css" => ['style.css'],
                        ]
                    ],
                ],
                null,
                4
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'bootstrap'],
                ])]),
                [
                    'entrypointsJsons' => [
                        'bootstrap' => [
                            "js" => ['boostrap.js'],
                            "css" => ['style.css'],
                        ]
                    ],
                ],
                null,
                1
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'bootstrap'],
                ])]),
                [
                    'entries' => 5,
                    'entrypointsJsons' => [
                        'bootstrap' => [
                            "js" => ['boostrap.js'],
                            "css" => ['style.css'],
                        ]
                    ],
                ],
                null,
                1
            ],
        ];
    }

    /**
     * @dataProvider entryPointProvider
     */
    public function testGeneratePageEntrypoints(bool $returnValue, $pageModel, $layoutModel, $bundleConfig, $encoreField, $count = 0)
    {
        if (!empty($bundleConfig)) {
            $bundleConfig = $bundleConfig;
        }

        $pageEntrypoints = $this->mockPageEntrypointsObject($bundleConfig);
        if ($returnValue) {
            $this->assertTrue($pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel, $encoreField));
            $this->assertCount($count, $pageEntrypoints->getActiveEntries());
        }
        else {
            $this->assertFalse($pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel, $encoreField));
        }
    }

    public function testGetterNotInitialized()
    {
        $this->expectException(\Exception::class);
        $pageEntrypoints = $this->mockPageEntrypointsObject();
        $pageEntrypoints->getActiveEntries();
    }

    public function testGetter()
    {
        $pageModel = $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
            ['entry' => 'contao-a-bundle', 'active' => '1'],
            ['entry' => 'contao-b-bundle', 'active' => ''],
        ])]);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
            ['entry' => 'contao-encore-bundle'],
            ['entry' => 'bootstrap'],
        ])]);
        $config = [
            'entries'          => [
                ['name' => 'contao-encore-bundle', 'head' => '1'],
                ['name' => 'contao-slick-bundle', 'requiresCss' => '1'],
                ['name' => 'contao-a-bundle'],
                ['name' => 'contao-b-bundle'],
            ],
            'entrypointsJsons' => [
                'bootstrap' => [
                    "js"  => ['boostrap.js'],
                    "css" => ['style.css'],
                ]
            ],
        ];
        $pageEntrypoints = $this->mockPageEntrypointsObject($config);
        $pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel);

        $this->assertCount(4, $pageEntrypoints->getActiveEntries());
        $this->assertCount(2, $pageEntrypoints->getCssEntries());
        $this->assertCount(1, $pageEntrypoints->getJsHeadEntries());
        $this->assertCount(3, $pageEntrypoints->getJsEntries());

    }
}
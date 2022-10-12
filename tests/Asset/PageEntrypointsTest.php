<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\Error\Warning;

class PageEntrypointsTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function createTestInstance(array $parameter = [])
    {
        $container = $parameter['container'] ?? $this->getContainerWithContaoConfiguration();

        if (isset($parameter['frontendAsset'])) {
            $frontendAsset = $parameter['frontendAsset'];
        } else {
            $frontendAsset = $this->createMock(FrontendAsset::class);
            $frontendAsset->method('getActiveEntrypoints')->willReturn([]);
        }

        if (isset($parameter['modelUtil'])) {
            $modelUtil = $parameter['modelUtil'];
        } else {
            $modelUtil = $this->createMock(ModelUtil::class);
            $modelUtil->method('findParentsRecursively')->willReturn([]);
        }

        if (isset($parameter['arrayUtil'])) {
            $arrayUtil = $parameter['arrayUtil'];
        } else {
            $container = $this->getContainerWithContaoConfiguration();
            $container->set('contao.framework', $this->mockContaoFramework());
            $arrayUtil = new ArrayUtil($container);
        }

        if (isset($parameter['entryCollection'])) {
            $entryCollection = $parameter['entryCollection'];
        } else {
            $entryCollection = $this->createMock(EntryCollection::class);

            if (isset($parameter['bundleConfig'])) {
                $bundleConfig = $parameter['bundleConfig'];
                if (isset($bundleConfig['entrypoints_jsons']) && \is_array($bundleConfig['entrypoints_jsons'])) {
                    array_walk(
                        $bundleConfig['entrypoints_jsons'],
                        function (&$item, $key) {
                            $item = [
                                'name' => $key,
                                'requires_css' => isset($item['css']),
                            ];
                        }
                    );
                }
                $entryCollection->method('getEntries')->willReturn(
                    array_merge(($bundleConfig['js_entries'] ?? []), ($bundleConfig['entrypoints_jsons'] ?? []))
                );
            }
        }

        return new PageEntrypoints($container, $frontendAsset, $entryCollection, $arrayUtil, $modelUtil);
    }

    public function entryPointProvider()
    {
        return [
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                [],
                null,
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => []],
                null,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [[], []]],
                null,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['a'], ['b']]],
                null,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['name' => 'contao-encore-bundle'], ['name' => 'b']]],
                null,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['name' => 'contao-encore-bundle'], ['name' => 'contao-slick-bundle']]],
                null,
                1,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([['entry' => 'contao-encore-bundle']])]),
                ['js_entries' => [['name' => 'contao-encore-bundle'], ['name' => 'contao-slick-bundle']]],
                null,
                2,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([['entry' => 'contao-encore-bundle']])]),
                ['js_entries' => [
                    ['name' => 'contao-encore-bundle', 'head' => '1'],
                    ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                    ['name' => 'contao-a-bundle'],
                    ['name' => 'contao-b-bundle'],
                ]],
                null,
                3,
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
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle', 'head' => '1'],
                        ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ],
                    'entrypoints_jsons' => [
                        'bootstrap' => [
                            'js' => ['boostrap.js'],
                            'css' => ['style.css'],
                        ],
                    ],
                ],
                null,
                4,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'bootstrap'],
                    ['entry' => 'contao-encore-bundle'],
                ])]),
                [
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle', 'head' => '1'],
                        ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ],
                    'entrypoints_jsons' => [
                        'bootstrap' => [
                            'js' => ['boostrap.js'],
                            'css' => ['style.css'],
                        ],
                    ],
                ],
                null,
                4,
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                    ['entry' => 'contao-slick-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'bootstrap'],
                ])]),
                [
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle', 'head' => '1'],
                        ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ],
                    'entrypoints_jsons' => [
                        'bootstrap' => [
                            'js' => ['boostrap.js'],
                            'css' => ['style.css'],
                        ],
                    ],
                ],
                null,
                3,
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
                    'entrypoints_jsons' => [
                        'bootstrap' => [
                            'js' => ['boostrap.js'],
                            'css' => ['style.css'],
                        ],
                    ],
                ],
                null,
                1,
            ],
        ];
    }

    /**
     * @dataProvider entryPointProvider
     */
    public function testGeneratePageEntrypoints(bool $returnValue, $pageModel, $layoutModel, $bundleConfig, $encoreField, $count = 0)
    {
        if (isset($bundleConfig['entrypoints_jsons']) && \is_array($bundleConfig['entrypoints_jsons'])) {
            array_walk(
                $bundleConfig['entrypoints_jsons'],
                function (&$item, $key) {
                    $item = [
                        'name' => $key,
                    ];
                }
            );
        }

        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->method('getEntries')->willReturn(
            array_merge(($bundleConfig['js_entries'] ?? []), ($bundleConfig['entrypoints_jsons'] ?? []))
        );

        $frontendAsset = new FrontendAsset();
        $frontendAsset->addActiveEntrypoint('contao-slick-bundle');

        $pageEntrypoints = $this->createTestInstance([
            'entryCollection' => $entryCollection,
            'frontendAsset' => $frontendAsset,
        ]);
        if ($returnValue) {
            $this->assertTrue($pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel, $encoreField));
            $this->assertCount($count, $pageEntrypoints->getActiveEntries());
        } else {
            $this->assertFalse($pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel, $encoreField));
        }
    }

    public function entryPointOrderProvider()
    {
        return [
            [
                [$this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-c-bundle', 'active' => ''],
                ])])],
                [
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle'],
                        ['name' => 'contao-slick-bundle'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ['name' => 'contao-c-bundle'],
                        ['name' => 'bootstrap'],
                    ],
                ],
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-c-bundle', 'active' => '1'],
                    ['entry' => 'contao-slick-bundle', 'active' => ''],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'bootstrap'],
                    ['entry' => 'contao-slick-bundle'],
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'contao-a-bundle'],
                    ['entry' => 'contao-b-bundle'],
                ])]),
                [
                    'bootstrap',
                    'contao-encore-bundle',
                    'contao-a-bundle',
                    'contao-c-bundle',
                ],
            ],
            [
                [$this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-c-bundle', 'active' => '1'],
                    ['entry' => 'contao-a-bundle', 'active' => '1'],
                    ['entry' => 'contao-b-bundle', 'active' => '1'],
                ])])],
                [
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle'],
                        ['name' => 'bootstrap'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ['name' => 'contao-c-bundle'],
                        ['name' => 'contao-slick-bundle'],
                    ],
                ],
                $this->mockModelObject(PageModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'contao-slick-bundle', 'active' => ''],
                    ['entry' => 'contao-b-bundle', 'active' => ''],
                ])]),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([
                    ['entry' => 'bootstrap'],
                    ['entry' => 'contao-slick-bundle'],
                    ['entry' => 'contao-encore-bundle'],
                    ['entry' => 'contao-a-bundle'],
                    ['entry' => 'contao-b-bundle'],
                ])]),
                [
                    'bootstrap',
                    'contao-encore-bundle',
                    'contao-c-bundle',
                    'contao-a-bundle',
                ],
            ],
        ];
    }

    /**
     * @dataProvider entryPointOrderProvider
     *
     * @param $pageParents
     *
     * @throws \Exception
     */
    public function testPageEntryOrder($pageParents, $bundleConfig, $page, $layout, $result)
    {
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findParentsRecursively')->willReturn($pageParents);

        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->method('getEntries')->willReturn(($bundleConfig['js_entries'] ?? []));

        $frontendAsset = new FrontendAsset();
        $frontendAsset->addActiveEntrypoint('contao-slick-bundle');

        $pageEntrypoints = $this->createTestInstance([
            'modelUtil' => $modelUtil,
            'entryCollection' => $entryCollection,
            'frontendAsset' => $frontendAsset,
        ]);

        $this->assertTrue($pageEntrypoints->generatePageEntrypoints($page, $layout));
        $this->assertSame($result, $pageEntrypoints->getJsEntries());
    }

    public function testGetterNotInitialized()
    {
        $this->expectException(\Exception::class);
        $pageEntrypoints = $this->createTestInstance();
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
            'js_entries' => [
                ['name' => 'contao-encore-bundle', 'head' => '1'],
                ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                ['name' => 'contao-a-bundle'],
                ['name' => 'contao-b-bundle'],
            ],
            'entrypoints_jsons' => [
                'bootstrap' => [
                    'js' => ['boostrap.js'],
                    'css' => ['style.css'],
                ],
            ],
        ];

        $frontendAsset = new FrontendAsset();
        $frontendAsset->addActiveEntrypoint('contao-slick-bundle');

        $pageEntrypoints = $this->createTestInstance([
            'bundleConfig' => $config,
            'frontendAsset' => $frontendAsset,
        ]);
        $pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel);

        $this->assertCount(4, $pageEntrypoints->getActiveEntries());
        $this->assertCount(2, $pageEntrypoints->getCssEntries());
        $this->assertCount(1, $pageEntrypoints->getJsHeadEntries());
        $this->assertCount(3, $pageEntrypoints->getJsEntries());
    }

    public function testCreateInstance()
    {
        $instance = $this->createTestInstance();
        $newInstance = $instance->createInstance();
        $this->assertInstanceOf(PageEntrypoints::class, $newInstance);
        $this->assertNotSame($instance, $newInstance);
    }

    public function testAlreadyInitializedWarning()
    {
        $page = $this->mockModelObject(PageModel::class, []);
        $layout = $this->mockClassWithProperties(LayoutModel::class, []);

        $instance = $this->createTestInstance(['bundleConfig' => ['js_entries' => [['name' => 'contao-encore-bundle'], ['name' => 'b']]]]);
        $instance->generatePageEntrypoints($page, $layout);

        $this->expectException(Warning::class);
        $instance->generatePageEntrypoints($page, $layout);
    }

    public function testLegacyAddBabelEntry()
    {
        $instance = $this->createTestInstance();
        $page = $this->mockModelObject(PageModel::class, []);
        $layout = $this->mockModelObject(LayoutModel::class, [
            'addEncoreBabelPolyfill' => '1',
            'encoreBabelPolyfillEntryName' => 'babel-polyfill',
        ]);
        $this->assertSame([
            ['entry' => 'babel-polyfill'],
        ], $instance->collectPageEntries($layout, $page));
    }
}

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
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\Error\Warning;

class PageEntrypointsTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function mockPageEntrypointsObject(array $parameter = [])
    {
        if (!isset($parameter['bundleConfig'])) {
            $parameter['bundleConfig'] = [];
        }
        if (!isset($parameter['container'])) {
            $container = $this->mockContainer();
        } else {
            $container = $parameter['container'];
        }
        if (!isset($parameter['framework'])) {
            $container->set('contao.framework', $this->mockContaoFramework());
        } else {
            $container->set('contao.framework', $parameter['framework']);
        }

        if (!$container->has('huh.utils.model')) {
            $modelUtil = $this->createMock(ModelUtil::class);
            $modelUtil->method('findParentsRecursively')->willReturn([]);
            $container->set('huh.utils.model', $modelUtil);
        }
        if (!$container->has('huh.utils.array')) {
            $arrayUtil = new ArrayUtil($container);
            $entrypointsJsonLookup = $this->createMock(EntrypointsJsonLookup::class);
            $entrypointsJsonLookup->method('mergeEntries')->willReturnCallback(function (array $entrypointsJsons, array $entries, LayoutModel $layout = null) {
                foreach ($entrypointsJsons as $name => $entrypoint) {
                    $entry = ['name' => $name];
                    if (isset($entrypoint['css'])) {
                        $entry['requires_css'] = '1';
                    }
                    $entries[] = $entry;
                }
                return $entries;
            });
            $container->set('huh.utils.array', $arrayUtil);
        }

        $frontendAsset = new FrontendAsset();
        $frontendAsset->addActiveEntrypoint('contao-slick-bundle');
        return new PageEntrypoints($parameter['bundleConfig'], $entrypointsJsonLookup, $container, $frontendAsset);
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
                ['js_entries' => ''],
                null
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => 123],
                null
            ],
            [
                false,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => []],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [[],[]]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['a'],['b']]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['name' => 'contao-encore-bundle'],['name' => 'b']]],
                null
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, []),
                ['js_entries' => [['name' => 'contao-encore-bundle'],['name' => 'contao-slick-bundle']]],
                null,
                1
            ],
            [
                true,
                $this->mockModelObject(PageModel::class, []),
                $this->mockClassWithProperties(LayoutModel::class, ['encoreEntries' => serialize([['entry' => 'contao-encore-bundle']])]),
                ['js_entries' => [['name' => 'contao-encore-bundle'],['name' => 'contao-slick-bundle']]],
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
                ['js_entries' => [
                    ['name' => 'contao-encore-bundle', 'head' => '1'],
                    ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
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
                    'js_entries' => [
                        ['name' => 'contao-encore-bundle', 'head' => '1'],
                        ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                        ['name' => 'contao-a-bundle'],
                        ['name' => 'contao-b-bundle'],
                        ],
                    'entrypoints_jsons' => [
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
                            "js" => ['boostrap.js'],
                            "css" => ['style.css'],
                        ]
                    ],
                ],
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
                    'entrypoints_jsons' => [
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
                    'js_entries' => 5,
                    'entrypoints_jsons' => [
                        'bootstrap' => [
                            "js" => ['boostrap.js'],
                            "css" => ['style.css'],
                        ]
                    ],
                ],
                null,
                0
            ],
        ];
    }

    /**
     * @dataProvider entryPointProvider
     */
    public function testGeneratePageEntrypoints(bool $returnValue, $pageModel, $layoutModel, $bundleConfig, $encoreField, $count = 0)
    {
        $pageEntrypoints = $this->mockPageEntrypointsObject(['bundleConfig' => $bundleConfig]);
        if ($returnValue) {
            $this->assertTrue($pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel, $encoreField));
            $this->assertCount($count, $pageEntrypoints->getActiveEntries());
        }
        else {
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
                    ]
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
                ]
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
                    ]
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
                ]
            ],
        ];
    }

    /**
     *
     * @dataProvider entryPointOrderProvider
     * @param $pageParents
     * @throws \Exception
     */
    public function testPageEntryOrder($pageParents, $bundleConfig, $page, $layout, $result)
    {
        $container = $this->mockContainer();
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findParentsRecursively')->willReturn($pageParents);
        $container->set('huh.utils.model', $modelUtil);

        $pageEntrypoints = $this->mockPageEntrypointsObject([
            'bundleConfig' => $bundleConfig,
            'container' => $container,
        ]);

        $this->assertTrue($pageEntrypoints->generatePageEntrypoints($page, $layout));
        $this->assertSame($result, $pageEntrypoints->getJsEntries());


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
            'js_entries'          => [
                ['name' => 'contao-encore-bundle', 'head' => '1'],
                ['name' => 'contao-slick-bundle', 'requires_css' => '1'],
                ['name' => 'contao-a-bundle'],
                ['name' => 'contao-b-bundle'],
            ],
            'entrypoints_jsons' => [
                'bootstrap' => [
                    "js"  => ['boostrap.js'],
                    "css" => ['style.css'],
                ]
            ],
        ];
        $pageEntrypoints = $this->mockPageEntrypointsObject(['bundleConfig' => $config]);
        $pageEntrypoints->generatePageEntrypoints($pageModel, $layoutModel);

        $this->assertCount(4, $pageEntrypoints->getActiveEntries());
        $this->assertCount(2, $pageEntrypoints->getCssEntries());
        $this->assertCount(1, $pageEntrypoints->getJsHeadEntries());
        $this->assertCount(3, $pageEntrypoints->getJsEntries());

    }

    public function testCreateInstance()
    {
        $instance = $this->mockPageEntrypointsObject();
        $newInstance = $instance->createInstance();
        $this->assertInstanceOf(PageEntrypoints::class, $newInstance);
        $this->assertNotSame($instance, $newInstance);
    }

    public function testAlreadyInitializedWarning()
    {
        $page = $this->mockModelObject(PageModel::class, []);
        $layout = $this->mockClassWithProperties(LayoutModel::class, []);

        $instance = $this->mockPageEntrypointsObject(['bundleConfig' => ['js_entries' => [['name' => 'contao-encore-bundle'],['name' => 'b']]]]);
        $instance->generatePageEntrypoints($page, $layout);

        $this->expectException(Warning::class);
        $instance->generatePageEntrypoints($page, $layout);
    }

    public function testLegacyAddBabelEntry()
    {
        $instance = $this->mockPageEntrypointsObject();
        $page = $this->mockModelObject(PageModel::class, []);
        $layout = $this->mockModelObject(LayoutModel::class, [
            'addEncoreBabelPolyfill' => '1',
            'encoreBabelPolyfillEntryName' => 'babel-polyfill',
        ]);
        $this->assertSame([
            ['entry' => 'babel-polyfill'],
            ['entry' => 'contao-slick-bundle']
        ], $instance->collectPageEntries($layout, $page));
    }


}
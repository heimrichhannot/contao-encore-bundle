<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollectionFactory;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;

class EntrypointCollectionFactoryTest extends ContaoTestCase
{
    public function testCreateCollection()
    {
        $bundleConfig = [];
        $arrayUtil = $this->createMock(ArrayUtil::class);
        $instance = new EntrypointCollectionFactory($bundleConfig, $arrayUtil);
        $collection = $instance->createCollection([]);
        $this->assertEmpty($collection->getActiveEntries());

        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle', 'active' => false],
        ]);
        $this->assertEmpty($collection->getActiveEntries());

        $collection = $instance->createCollection([
            ['contao-amce-bundle', 'active' => true],
        ]);
        $this->assertEmpty($collection->getActiveEntries());

        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle', 'active' => false],
            ['entry' => 'contao-amce-bundle', 'active' => true],
        ]);
        $this->assertEmpty($collection->getActiveEntries());

        $bundleConfig = [
            'js_entries' => ['entries'],
        ];
        $arrayUtil = $this->createMock(ArrayUtil::class);
        $arrayUtil->method('getArrayRowByFieldValue')
            ->willReturnCallback(function ($key, $value, array $haystack) {
                switch ($value) {
                    case 'contao-amce-bundle':
                        return [
                            'name' => 'contao-amce-bundle',
                        ];
                    case 'contao-test-bundle':
                        return [
                            'name' => 'contao-test-bundle',
                            'requires_css' => true,
                        ];
                    case 'contao-head-bundle':
                        return [
                            'name' => 'contao-head-bundle',
                            'requires_css' => false,
                            'head' => true,
                        ];
                }
            });
        $instance = new EntrypointCollectionFactory($bundleConfig, $arrayUtil);
        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle', 'active' => false],
            ['entry' => 'contao-amce-bundle', 'active' => true],
        ]);
        $this->assertCount(1, $collection->getActiveEntries());

        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle'],
            ['entry' => 'contao-test-bundle'],
        ]);
        $this->assertCount(2, $collection->getActiveEntries());
        $this->assertSame(['contao-test-bundle'], $collection->getCssEntries());

        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle'],
            ['entry' => 'contao-test-bundle'],
            ['entry' => 'contao-head-bundle'],
            ['entry' => 'contao-old-bundle'],
        ]);
        $this->assertCount(3, $collection->getActiveEntries());
        $this->assertCount(2, $collection->getJsEntries());
        $this->assertCount(1, $collection->getJsHeadEntries());
        $this->assertSame(['contao-test-bundle'], $collection->getCssEntries());

        $this->assertCount(3, $collection->getTemplateData());
    }
}

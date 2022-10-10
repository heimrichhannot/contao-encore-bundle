<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollectionFactory;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;

class EntrypointCollectionFactoryTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $arrayUtil = $parameters['arrayUtil'] ?? $this->createMock(ArrayUtil::class);
        $entryCollection = $parameters['entryCollection'] ?? $this->createMock(EntryCollection::class);

        return new EntrypointCollectionFactory($arrayUtil, $entryCollection);
    }

    public function testCreateCollection()
    {
        $instance = $this->createTestInstance();
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
        $entryCollectionMock = $this->createMock(EntryCollection::class);
        $entryCollectionMock->method('getEntries')->willReturn(['entries']);

        $instance = $this->createTestInstance([
            'arrayUtil' => $arrayUtil,
            'entryCollection' => $entryCollectionMock,
        ]);
        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle', 'active' => false],
            ['entry' => 'contao-amce-bundle', 'active' => true],
        ]);
        $this->assertCount(1, $collection->getActiveEntries());

        return;

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

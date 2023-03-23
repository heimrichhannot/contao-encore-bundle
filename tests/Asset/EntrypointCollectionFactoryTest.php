<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollectionFactory;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;

class EntrypointCollectionFactoryTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $entryCollection = $parameters['entryCollection'] ?? $this->createMock(EntryCollection::class);

        return new EntrypointCollectionFactory($entryCollection);
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

        $entryCollectionMock = $this->createMock(EntryCollection::class);
        $entryCollectionMock->method('getEntries')->willReturn([
            ['name' => 'contao-amce-bundle'],
            ['name' => 'contao-test-bundle', 'requires_css' => true],
            ['name' => 'contao-head-bundle', 'requires_css' => false, 'head' => true],
        ]);

        $instance = $this->createTestInstance([
            'entryCollection' => $entryCollectionMock,
        ]);
        $collection = $instance->createCollection([
            ['entry' => 'contao-amce-bundle', 'active' => false],
            ['entry' => 'contao-amce-bundle', 'active' => true],
        ]);
        $this->assertCount(1, $collection->getActiveEntries());

//        return;
//
//        $collection = $instance->createCollection([
//            ['entry' => 'contao-amce-bundle'],
//            ['entry' => 'contao-test-bundle'],
//        ]);
//        $this->assertCount(2, $collection->getActiveEntries());
//        $this->assertSame(['contao-test-bundle'], $collection->getCssEntries());
//
//        $collection = $instance->createCollection([
//            ['entry' => 'contao-amce-bundle'],
//            ['entry' => 'contao-test-bundle'],
//            ['entry' => 'contao-head-bundle'],
//            ['entry' => 'contao-old-bundle'],
//        ]);
//        $this->assertCount(3, $collection->getActiveEntries());
//        $this->assertCount(2, $collection->getJsEntries());
//        $this->assertCount(1, $collection->getJsHeadEntries());
//        $this->assertSame(['contao-test-bundle'], $collection->getCssEntries());
//
//        $this->assertCount(3, $collection->getTemplateData());
    }
}

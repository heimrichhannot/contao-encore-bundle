<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Collection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\ConfigurationCollection;
use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

class ConfigurationCollectionTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $extensionCollection = $parameters['extensionCollection'] ?? $this->createMock(ExtensionCollection::class);

        return new ConfigurationCollection($extensionCollection);
    }

    public function testGetJsEntries()
    {
        $bundle1Extension = $this->createMock(EncoreExtensionInterface::class);
        $bundle1Extension->method('getEntries')->willReturn([EncoreEntry::create('bundle1', 'path')]);

        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->method('getExtensions')->willReturn([]);

        $instance = $this->createTestInstance(['extensionCollection' => $extensionCollection]);
        $this->assertEmpty($instance->getJsEntries());
        $this->assertEmpty($instance->getJsEntries(['array' => true]));

        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->method('getExtensions')->willReturn([$bundle1Extension]);

        $instance = $this->createTestInstance(['extensionCollection' => $extensionCollection]);
        $this->assertCount(1, $instance->getJsEntries());
        $this->assertInstanceOf(EncoreEntry::class, $instance->getJsEntries()[0]);
        $this->assertCount(1, $instance->getJsEntries(['array' => false]));
        $this->assertInstanceOf(EncoreEntry::class, $instance->getJsEntries(['array' => false])[0]);
        $this->assertCount(1, $instance->getJsEntries(['array' => true]));
        $this->assertIsArray($instance->getJsEntries(['array' => true])[0]);

        $instance = $this->createTestInstance([
            'extensionCollection' => $extensionCollection,
        ]);
        $this->assertCount(1, $instance->getJsEntries());
        $this->assertInstanceOf(EncoreEntry::class, $instance->getJsEntries()[0]);

        $this->assertCount(1, $instance->getJsEntries(['array' => true]));
        $this->assertIsArray($instance->getJsEntries(['array' => true])[0]);
    }
}

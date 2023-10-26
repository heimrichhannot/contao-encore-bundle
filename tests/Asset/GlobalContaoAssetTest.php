<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

class GlobalContaoAssetTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $bundleConfig = $parameters['bundleConfig'] ?? [];
        $extensionCollection = $parameters['extensionCollection'] ?? $this->createMock(ExtensionCollection::class);

        return new GlobalContaoAsset($bundleConfig, $extensionCollection);
    }

    public function testCleanGlobalArrayFromConfiguration()
    {
        //
        // Clean nothing
        //

        $GLOBALS['TL_CSS'] = [
            'foo' => 'bar',
        ];

        $instance = $this->createTestInstance();
        $instance->cleanGlobalArrayFromConfiguration();
        $this->assertSame($GLOBALS['TL_CSS'], [
            'foo' => 'bar',
        ]);

        //
        // clean from extension
        //
        $GLOBALS['TL_CSS'] = ['foo' => 'bar', 'hello' => 'world'];
        $GLOBALS['TL_JAVASCRIPT'] = ['test' => 'try', 'a' => 'b', 'c' => 'd'];

        $entry1 = EncoreEntry::create('hello1', 'world');
        $entry2 = EncoreEntry::create('hello2', 'world')->addCssEntryToRemoveFromGlobals('hello');
        $entry3 = EncoreEntry::create('hello3', 'world')->addCssEntryToRemoveFromGlobals('hello');
        $entry4 = EncoreEntry::create('hello4', 'world')->addJsEntryToRemoveFromGlobals('a');

        $extension1 = $this->createMock(EncoreExtensionInterface::class);
        $extension1->method('getEntries')->willReturn([$entry1, $entry2]);
        $extension2 = $this->createMock(EncoreExtensionInterface::class);
        $extension2->method('getEntries')->willReturn([$entry3, $entry4]);

        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->method('getExtensions')->willReturn([$extension1, $extension2]);

        $instance = $this->createTestInstance([
            'extensionCollection' => $extensionCollection,
        ]);

        $instance->cleanGlobalArrayFromConfiguration();

        $this->assertSame($GLOBALS['TL_CSS'], ['foo' => 'bar']);
        $this->assertSame($GLOBALS['TL_JAVASCRIPT'], ['test' => 'try', 'c' => 'd']);

        //
        // Remove jquery
        //

        $GLOBALS['TL_JAVASCRIPT'] = ['assets/jquery/js/jquery.min.js|static'];

        $bundleConfig = ['unset_jquery' => true];
        $instance = $this->createTestInstance([
            'bundleConfig' => $bundleConfig,
        ]);
        $instance->cleanGlobalArrayFromConfiguration();
        $this->assertEmpty($GLOBALS['TL_JAVASCRIPT']);

        $exception = false;
        try {
            $instance->cleanFromGlobalArray('TL_DCA', ['hey']);
        } catch (\InvalidArgumentException $e) {
            $exception = true;
        }
        $this->assertTrue($exception);
    }
}

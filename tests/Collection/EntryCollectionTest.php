<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Collection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\ConfigurationCollection;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Exception\NoEntrypointsException;
use Psr\Cache\CacheItemPoolInterface;

class EntryCollectionTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $bundleConfig = $parameters['bundleConfig'] ?? [];
        $cache = $parameters['cache'] ?? $this->createMock(CacheItemPoolInterface::class);

        if (!isset($parameters['configurationCollection'])) {
            $configurationCollection = $this->createMock(ConfigurationCollection::class);
            $configurationCollection->method('getJsEntries')->willReturn([]);
        } else {
            $configurationCollection = $parameters['configurationCollection'];
        }

        return new EntryCollection($configurationCollection, $bundleConfig, $cache);
    }

    public function testGetEntries()
    {
        $instance = $this->createTestInstance();
        $this->assertEmpty($instance->getEntries());

        $bundleConfig = [
            'entrypoints_jsons' => [
                __DIR__.'/../Fixtures/no_entrypoints.json',
            ],
        ];

        $instance = $this->createTestInstance([
            'bundleConfig' => $bundleConfig,
        ]);

        $exception = false;
        try {
            $instance->getEntries();
        } catch (NoEntrypointsException $entrypointsException) {
            $exception = true;
        }
        $this->assertTrue($exception);

        $bundleConfig = [
            'entrypoints_jsons' => [
                __DIR__.'/../Fixtures/entrypoints.json',
            ],
        ];

        $instance = $this->createTestInstance([
            'bundleConfig' => $bundleConfig,
        ]);

        $this->assertCount(4, $instance->getEntries());

        $configurationCollection = $this->createMock(ConfigurationCollection::class);
        $configurationCollection->method('getJsEntries')->willReturn([
            ['name' => 'contao-acme-bundle', 'file' => 'somefile'],
            ['name' => 'contao-list-bundle', 'file' => '/assets/js/list-bundle.js'],
        ]);

        $instance = $this->createTestInstance([
            'bundleConfig' => $bundleConfig,
            'configurationCollection' => $configurationCollection,
        ]);

        $this->assertCount(5, $instance->getEntries());
    }
}

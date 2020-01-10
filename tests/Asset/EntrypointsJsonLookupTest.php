<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class EntrypointsJsonLookupTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $container;

    /**
     * @var EntrypointsJsonLookup
     */
    private $lookup;

    public function createTestInstance(array $parameters = [])
    {
        if (!isset($parameters['config'])) {
            $parameters['config'] = [];
        }
        if (!isset($parameters['cache'])) {
            $parameters['cache'] = null;
        }

        $lookup = new EntrypointsJsonLookup($parameters['config'], $parameters['cache']);
        return $lookup;
    }

    public function testMergeEntries()
    {
        $lookup = $this->createTestInstance();
        $entries = $lookup->mergeEntries([], [
            [
                'name' => 'contao-project-bundle',
            ],
        ]);

        $this->assertCount(1, $entries);

        $entries = $lookup->mergeEntries([
                __DIR__.'/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle',
                ],
            ]);

        $this->assertCount(3, $entries);
        $this->assertSame([
            [
                'name' => 'contao-project-bundle',
            ],
            [
                'name' => 'main',
                'head' => false,
                'requires_css' => true,
            ],
            [
                'name' => 'babel-polyfill',
                'head' => false,
            ],
        ], $entries);

        $entries = $lookup->mergeEntries([
                __DIR__.'/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle',
                ],
            ]);

        $this->assertCount(3, $entries);
        $this->assertSame([
            [
                'name' => 'contao-project-bundle',
            ],
            [
                'name' => 'main',
                'head' => false,
                'requires_css' => true,
            ],
            [
                'name' => 'babel-polyfill',
                'head' => false,
            ],
        ], $entries);

        $entries = $lookup->mergeEntries([
                __DIR__.'/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle',
                ],
                [
                    'file' => 'contao-some-file.js',
                ],
            ]);

        $this->assertCount(4, $entries);
        $this->assertSame([
            [
                'name' => 'contao-project-bundle',
            ],
            [
                'file' => 'contao-some-file.js',
            ],
            [
                'name' => 'main',
                'head' => false,
                'requires_css' => true,
            ],
            [
                'name' => 'babel-polyfill',
                'head' => false,
            ],
        ], $entries);
    }

    public function testParseEntrypoints()
    {
        $lookup = $this->createTestInstance();
        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(3, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
        $this->assertArrayHasKey('babel-polyfill', $entrypoints);
        $this->assertArrayHasKey('contao-project-bundle', $entrypoints);
    }

    public function testParseEntrypointsNoFile()
    {
        $lookup = $this->createTestInstance();
        $this->expectException(\InvalidArgumentException::class);

        $lookup->parseEntrypoints(__DIR__.'/../notexisting.json');
    }

    public function testParseEntrypointsCachedNoHit()
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $cache->expects(self::once())
            ->method('getItem')
            ->with('_default')
            ->willReturn($item);

        $item->expects(self::exactly(2))
            ->method('isHit')
            ->willReturn(false);
        $item->expects(self::once())
            ->method('set')
            ->with(self::isType('array'))
            ->willReturn($item);

        $cache->expects(self::once())
            ->method('save')
            ->with($item);

        $lookup = $this->createTestInstance([
            'config' => ['encore_cache_enabled' => true],
            'cache' => $cache,
        ]);

        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(3, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
        $this->assertArrayHasKey('babel-polyfill', $entrypoints);
        $this->assertArrayHasKey('contao-project-bundle', $entrypoints);
    }

    public function testParseEntrypointsCachedWithHit()
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $cache->expects(self::once())
            ->method('getItem')
            ->with('_default')
            ->willReturn($item);

        $item->expects(self::exactly(1))
            ->method('isHit')
            ->willReturn(true);

        $item->expects(self::once())
            ->method('get')
            ->willReturn([
                'entrypoints' => [
                    'main' => [
                        'name' => 'main',
                    ],
                ],
            ]);

        $item->expects(self::never())
            ->method('set');
        $cache->expects(self::never())
            ->method('save');

        $lookup = $this->createTestInstance([
            'config' => ['encore_cache_enabled' => true],
            'cache' => $cache,
        ]);

        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(1, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
    }

    public function exceptionFileProvider()
    {
        return [['empty.json'],['no_entrypoints.json']];
    }

    /**
     * @dataProvider exceptionFileProvider
     */
    public function testParseEntrypointsException($file)
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $cache->method('getItem')
            ->with('_default')
            ->willReturn($item);

        $item->method('isHit')
            ->willReturn(false);
        $item->method('set')
            ->with(self::isType('array'))
            ->willReturn($item);

        $cache->method('save')
            ->with($item);

        $lookup = $this->createTestInstance([
            'config' => ['encore_cache_enabled' => true],
            'cache' => $cache,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../Fixtures/'.$file);
    }
}

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
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->lookup = new EntrypointsJsonLookup($this->container, null);
    }

    public function testMergeEntries()
    {
        $entries = $this->lookup->mergeEntries([], [
            [
                'name' => 'contao-project-bundle',
            ],
        ]);

        $this->assertCount(1, $entries);

        $entries = $this->lookup->mergeEntries([
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
                'requiresCss' => true,
            ],
            [
                'name' => 'babel-polyfill',
                'head' => false,
            ],
        ], $entries);

        $entries = $this->lookup->mergeEntries([
                __DIR__.'/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle',
                ],
            ],
            'babel-polyfill');

        $this->assertCount(2, $entries);
        $this->assertSame([
            [
                'name' => 'contao-project-bundle',
            ],
            [
                'name' => 'main',
                'head' => false,
                'requiresCss' => true,
            ],
        ], $entries);
    }

    public function testParseEntrypoints()
    {
        $entrypoints = $this->lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(3, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
        $this->assertArrayHasKey('babel-polyfill', $entrypoints);
        $this->assertArrayHasKey('contao-project-bundle', $entrypoints);
    }

    public function testParseEntrypointsNoFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->lookup->parseEntrypoints(__DIR__.'/../notexisting.json');
    }

    public function testParseEntrypointsCachedNoHit()
    {
        $this->setUpForCaching();

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

        $lookup = new EntrypointsJsonLookup($this->container, $cache);

        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(3, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
        $this->assertArrayHasKey('babel-polyfill', $entrypoints);
        $this->assertArrayHasKey('contao-project-bundle', $entrypoints);
    }

    public function testParseEntrypointsCachedWithHit()
    {
        $this->setUpForCaching();

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

        $lookup = new EntrypointsJsonLookup($this->container, $cache);

        $entrypoints = $lookup->parseEntrypoints(__DIR__.'/../entrypoints.json');

        $this->assertCount(1, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
    }

    protected function setUpForCaching()
    {
        $this->container->expects(self::once())
            ->method('hasParameter')
            ->with('huh.encore')
            ->willReturn(true);

        $this->container->expects(self::once())
            ->method('getParameter')
            ->with('huh.encore')
            ->willReturn([
                'encore' => [
                    'encoreCacheEnabled' => true,
                ],
            ]);
    }
}

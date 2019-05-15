<?php

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use PHPUnit\Framework\TestCase;

class EntrypointsJsonLookupTest extends TestCase
{
    public function testMergeEntries()
    {
        $lookup = new EntrypointsJsonLookup();
        $entries = $lookup->mergeEntries([], [
            [
                'name' => 'contao-project-bundle'
            ]
        ]);

        $this->assertCount(1, $entries);


        $entries = $lookup->mergeEntries([
                __DIR__ . '/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle'
                ]
            ]);

        $this->assertCount(3, $entries);
        $this->assertEquals([
            [
                'name' => 'contao-project-bundle'
            ],
            [
                'name' => 'main',
                'head' => false,
                'requiresCss' => true,
            ],
            [
                'name' => 'babel-polyfill',
                'head' => false,
            ]
        ], $entries);

        $entries = $lookup->mergeEntries([
                __DIR__ . '/../entrypoints.json',
            ], [
                [
                    'name' => 'contao-project-bundle'
                ]
            ],
            'babel-polyfill');

        $this->assertCount(2, $entries);
        $this->assertEquals([
            [
                'name' => 'contao-project-bundle'
            ],
            [
                'name' => 'main',
                'head' => false,
                'requiresCss' => true,
            ]
        ], $entries);
    }

    public function testParseEntrypoints()
    {
        $lookup = new EntrypointsJsonLookup();
        $entrypoints = $lookup->parseEntrypoints(__DIR__ . '/../entrypoints.json');

        $this->assertCount(3, $entrypoints);
        $this->assertArrayHasKey('main', $entrypoints);
        $this->assertArrayHasKey('babel-polyfill', $entrypoints);
        $this->assertArrayHasKey('contao-project-bundle', $entrypoints);
    }

    public function testParseEntrypointsNoFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        $lookup = new EntrypointsJsonLookup();
        $entrypoints = $lookup->parseEntrypoints(__DIR__ . '/../notexisting.json');
    }
}

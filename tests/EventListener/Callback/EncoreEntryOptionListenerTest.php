<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Callback;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

class EncoreEntryOptionListenerTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = []): EncoreEntryOptionListener
    {
        $entryCollection = $parameters['entryCollection'] ?? $this->createMock(EntryCollection::class);
        $translator = $parameters['translator'] ?? new Translator('en');

        return new EncoreEntryOptionListener($entryCollection, $translator);
    }

    public function testGetEntriesAsOptions()
    {
        $instance = $this->createTestInstance();
        $this->assertEmpty($instance->getEntriesAsOptions());

        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->expects($this->once())->method('getEntries')->with(false)->willReturn([
            EncoreEntry::create('hello', ''),
            EncoreEntry::create('abcd', 'abcd.js'),
        ]);

        $instance = $this->createTestInstance(['entryCollection' => $entryCollection]);
        $this->assertSame([
            'abcd' => 'abcd [abcd.js]',
            'hello' => 'hello',
        ], $instance->getEntriesAsOptions());
    }

    public function testTranslatedOptionsAreSortedByLabel(): void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['z_entry' => 'Alpha', 'a_entry' => 'Zulu'], 'en', 'encore_entry');

        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->method('getEntries')->with(false)->willReturn([
            EncoreEntry::create('a_entry', 'app.js'),
            EncoreEntry::create('z_entry', ''),
            EncoreEntry::create('missing_translation', ''),
        ]);

        $instance = $this->createTestInstance(['entryCollection' => $entryCollection, 'translator' => $translator]);

        $this->assertSame([
            'z_entry' => 'Alpha',
            'a_entry' => 'Zulu [app.js]',
            'missing_translation' => 'missing_translation',
        ], $instance->getEntriesAsOptions());
    }
}

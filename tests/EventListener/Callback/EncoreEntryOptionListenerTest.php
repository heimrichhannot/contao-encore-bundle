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
use Symfony\Contracts\Translation\TranslatorInterface;

class EncoreEntryOptionListenerTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = []): EncoreEntryOptionListener
    {
        $entryCollection = $parameters['entryCollection'] ?? $this->createMock(EntryCollection::class);
        $translator = $parameters['translator'] ?? $this->createMock(TranslatorInterface::class);

        return new EncoreEntryOptionListener($entryCollection, $translator);
    }

    public function testGetEntriesAsOptions()
    {
        $instance = $this->createTestInstance();
        $this->assertEmpty($instance->getEntriesAsOptions());

        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->method('getEntries')->willReturn([
            ['name' => 'hello'],
            ['name' => 'abcd', 'file' => 'abcd.js'],
        ]);

        $instance = $this->createTestInstance(['entryCollection' => $entryCollection]);
        $this->assertSame([
            'abcd' => 'abcd [abcd.js]',
            'hello' => 'hello',
        ], $instance->getEntriesAsOptions());
    }
}

<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Callback;

use HeimrichHannot\EncoreBundle\Collection\EntryCollection;

class EncoreEntryOptionListener
{
    private EntryCollection $entryCollection;

    public function __construct(EntryCollection $entryCollection)
    {
        $this->entryCollection = $entryCollection;
    }

    public function getEntriesAsOptions(): array
    {
        $choices = [];

        $projectEntries = $this->entryCollection->getEntries();

        if (empty($projectEntries)) {
            return $choices;
        }

        foreach ($projectEntries as $entry) {
            $choices[$entry['name']] = $entry['name'].(isset($entry['file']) ? ' ['.$entry['file'].']' : '');
        }

        asort($choices);

        return $choices;
    }
}

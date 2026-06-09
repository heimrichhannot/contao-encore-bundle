<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Callback;

use Contao\Message;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Exception\NoEntrypointsException;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use Symfony\Contracts\Translation\TranslatorInterface;

class EncoreEntryOptionListener
{
    public function __construct(
        private readonly EntryCollection $entryCollection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getEntriesAsOptions(): array
    {
        $choices = [];

        try {
            /** @var EncoreEntry[] $projectEntries */
            $projectEntries = $this->entryCollection->getEntries(false);
        } catch (NoEntrypointsException $e) {
            $projectEntries = [];
            Message::addError('[Encore Bundle] ' . $this->translator->trans('huh.encore.errors.noEntrypoints') . ' ' . $e->getMessage(), 'huh.encore.error.noEntryPoints');
        }

        if (empty($projectEntries)) {
            return $choices;
        }

        foreach ($projectEntries as $entry) {
            $title = $this->translator->trans($entry->name, domain: 'encore_entry');
            $choices[$entry->name] = $title . ('' !== $entry->path ? ' [' . $entry->path . ']' : '');
        }

        asort($choices);

        return $choices;
    }
}

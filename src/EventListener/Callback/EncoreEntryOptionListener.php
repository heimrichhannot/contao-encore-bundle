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
use Symfony\Contracts\Translation\TranslatorInterface;

class EncoreEntryOptionListener
{
    private EntryCollection     $entryCollection;
    private TranslatorInterface $translator;

    public function __construct(EntryCollection $entryCollection, TranslatorInterface $translator)
    {
        $this->entryCollection = $entryCollection;
        $this->translator = $translator;
    }

    public function getEntriesAsOptions(): array
    {
        $choices = [];

        try {
            $projectEntries = $this->entryCollection->getEntries();
        } catch (NoEntrypointsException $e) {
            $projectEntries = [];
            Message::addError('[Encore Bundle] '.$this->translator->trans('huh.encore.errors.noEntrypoints').' '.$e->getMessage(), 'huh.encore.error.noEntryPoints');
        }

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

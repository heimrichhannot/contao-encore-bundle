<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class EntryChoice extends AbstractChoice
{
    private EntryCollection         $entryCollection;

    public function __construct(ContaoFrameworkInterface $framework, EntryCollection $entryCollection)
    {
        parent::__construct($framework);
        $this->entryCollection = $entryCollection;
    }

    /**
     * @return array
     */
    protected function collect()
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

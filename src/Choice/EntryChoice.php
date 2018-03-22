<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class EntryChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.encore');

        if (!isset($config['encore']['entries'])) {
            return $choices;
        }

        foreach ($config['encore']['entries'] as $entry) {
            $choices[$entry['name']] = $entry['name'] . ' [' . $entry['file'] . ']';
        }

        asort($choices);

        return $choices;
    }
}

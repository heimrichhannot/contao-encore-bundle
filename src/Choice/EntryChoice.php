<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class EntryChoice extends AbstractChoice
{
    /**
     * @var EntrypointsJsonLookup
     */
    private $entrypointsJsonLookup;

    public function __construct(ContaoFrameworkInterface $framework, EntrypointsJsonLookup $entrypointsJsonLookup)
    {
        parent::__construct($framework);
        $this->entrypointsJsonLookup = $entrypointsJsonLookup;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.encore');

        // add entries from the entrypoints.json
        if (isset($config['encore']['entrypointsJsons']) && is_array($config['encore']['entrypointsJsons']) && !empty($config['encore']['entrypointsJsons'])) {
            if (!isset($config['encore']['entries'])) {
                $config['encore']['entries'] = [];
            } else if (!is_array($config['encore']['entries'])) {
                return $choices;
            }

            $dc = $this->getContext();
            $config['encore']['entries'] = $this->entrypointsJsonLookup->mergeEntries(
                $config['encore']['entrypointsJsons'],
                $config['encore']['entries'],
                $dc instanceof DataContainer && $dc->activeRecord != null ? $dc->activeRecord->encoreBabelPolyfillEntryName : null);
        }

        if (!isset($config['encore']['entries'])) {
            return $choices;
        }

        foreach ($config['encore']['entries'] as $entry) {
            $choices[$entry['name']] = $entry['name'] . (isset($entry['file']) ? ' [' . $entry['file'] . ']' : '');
        }

        asort($choices);

        return $choices;
    }
}

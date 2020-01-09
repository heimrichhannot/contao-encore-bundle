<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class EntryChoice extends AbstractChoice
{
    /**
     * @var EntrypointsJsonLookup
     */
    private $entrypointsJsonLookup;
    /**
     * @var array
     */
    private $bundleConfig;

    public function __construct(array $bundleConfig, ContaoFrameworkInterface $framework, EntrypointsJsonLookup $entrypointsJsonLookup)
    {
        parent::__construct($framework);
        $this->entrypointsJsonLookup = $entrypointsJsonLookup;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        // add entries from the entrypoints.json
        if (isset($this->bundleConfig['entrypoints_jsons']) && \is_array($this->bundleConfig['entrypoints_jsons']) && !empty($this->bundleConfig['entrypoints_jsons'])) {
            if (!isset($this->bundleConfig['entries'])) {
                $this->bundleConfig['entries'] = [];
            } elseif (!\is_array($this->bundleConfig['entries'])) {
                return $choices;
            }

            $dc = $this->getContext();
            $this->bundleConfig['entries'] = $this->entrypointsJsonLookup->mergeEntries(
                $this->bundleConfig['entrypoints_jsons'],
                $this->bundleConfig['entries']
            );
        }

        if (!isset($this->bundleConfig['entries'])) {
            return $choices;
        }

        foreach ($this->bundleConfig['entries'] as $entry) {
            $choices[$entry['name']] = $entry['name'].(isset($entry['file']) ? ' ['.$entry['file'].']' : '');
        }

        asort($choices);

        return $choices;
    }
}

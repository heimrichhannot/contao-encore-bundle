<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;

class EntrypointCollectionFactory
{
    protected ArrayUtil     $arrayUtil;
    private EntryCollection $entryCollection;

    /**
     * EntrypointCollectionFactory constructor.
     */
    public function __construct(ArrayUtil $arrayUtil, EntryCollection $entryCollection)
    {
        $this->arrayUtil = $arrayUtil;
        $this->entryCollection = $entryCollection;
    }

    public function createCollection(array $entrypoints): EntrypointCollection
    {
        $collection = new EntrypointCollection();
        if (empty($this->entryCollection->getEntries())) {
            return $collection;
        }

        foreach ($entrypoints as $entrypoint) {
            if (isset($entrypoint['active']) && !$entrypoint['active'] || !isset($entrypoint['entry'])) {
                continue;
            }
            if (!($entry = $this->arrayUtil->getArrayRowByFieldValue('name', $entrypoint['entry'], $this->entryCollection->getEntries()))) {
                continue;
            }

            if (isset($entry['head']) && $entry['head']) {
                $collection->addJsHeadEntry($entry['name']);
            } else {
                $collection->addJsEntry($entry['name']);
            }

            if (isset($entry['requires_css']) && $entry['requires_css']) {
                $collection->addCssEntry($entry['name']);
            }
        }

        return $collection;
    }
}

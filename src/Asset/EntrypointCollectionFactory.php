<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Helper\ArrayHelper;

class EntrypointCollectionFactory
{
    public function __construct(
        private readonly EntryCollection $entryCollection,
    ) {
    }

    public function createCollection(array $entrypoints): EntrypointCollection
    {
        $collection = new EntrypointCollection();
        if (empty($this->entryCollection->getEntries(false))) {
            return $collection;
        }

        foreach ($entrypoints as $entrypoint) {
            if (isset($entrypoint['active']) && !$entrypoint['active'] || !isset($entrypoint['entry'])) {
                continue;
            }
            if (!($entry = ArrayHelper::getArrayRowByFieldValue('name', $entrypoint['entry'], $this->entryCollection->getEntries(true)))) {
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

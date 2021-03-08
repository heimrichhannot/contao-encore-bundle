<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;

class EntrypointCollectionFactory
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var ArrayUtil
     */
    protected $arrayUtil;

    /**
     * EntrypointCollectionFactory constructor.
     */
    public function __construct(array $bundleConfig, ArrayUtil $arrayUtil)
    {
        $this->bundleConfig = $bundleConfig;
        $this->arrayUtil = $arrayUtil;
    }

    public function createCollection(array $entrypoints): EntrypointCollection
    {
        $collection = new EntrypointCollection();
        foreach ($entrypoints as $entrypoint) {
            if (isset($entrypoint['active']) && !$entrypoint['active']) {
                continue;
            }
            if (!isset($entrypoint['entry']) || !isset($this->bundleConfig['js_entries'])) {
                continue;
            }
            if (!($entry = $this->arrayUtil->getArrayRowByFieldValue('name', $entrypoint['entry'], $this->bundleConfig['js_entries']))) {
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

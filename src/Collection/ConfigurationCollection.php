<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Collection;

use HeimrichHannot\EncoreContracts\EncoreEntry;

class ConfigurationCollection
{
    private ExtensionCollection $extensionCollection;

    public function __construct(ExtensionCollection $extensionCollection)
    {
        $this->extensionCollection = $extensionCollection;
    }

    /**
     * Return all registered entrypoints.
     *
     * Options:
     * - array: (bool) Return entrypoints as array[] instead as EncoreEntry[]
     *
     * @param array{
     *     array: bool
     * } $options
     * @return array|EncoreEntry[]|array[]
     */
    public function getJsEntries(array $options = []): array
    {
        $options = array_merge([
            'array' => false,
        ], $options);

        $entrypoints = [];
        foreach ($this->extensionCollection->getExtensions() as $extension) {
            if ($options['array']) {
                foreach ($extension->getEntries() as $entry) {
                    $entrypoints[] = [
                        'name' => $entry->getName(),
                        'file' => $entry->getPath(),
                        'requires_css' => $entry->getRequiresCss(),
                        'head' => $entry->getIsHeadScript(),
                    ];
                }
            } else {
                $entrypoints = array_merge($entrypoints, $extension->getEntries());
            }
        }

        return $entrypoints;
    }
}

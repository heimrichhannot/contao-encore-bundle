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
    private array               $bundleConfig;
    private ExtensionCollection $extensionCollection;

    public function __construct(array $bundleConfig, ExtensionCollection $extensionCollection)
    {
        $this->bundleConfig = $bundleConfig;
        $this->extensionCollection = $extensionCollection;
    }

    /**
     * Return all registered entrypoints.
     *
     * Options:
     * - array: (bool) Return entrypoints as array[] instead as EncoreEntry[]
     *
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

        if ($options['array']) {
            $entrypoints = array_merge($entrypoints, $this->bundleConfig['js_entries']);
        } else {
            foreach (($this->bundleConfig['js_entries'] ?? []) as $key => $value) {
                $entry = EncoreEntry::create($value['name'], $value['file']);
                if ($value['requires_css'] ?? false) {
                    $entry->setRequiresCss(true);
                }
                if ($value['head'] ?? false) {
                    $entry->setIsHeadScript(true);
                }
                $entrypoints[] = $entry;
            }
        }

        return $entrypoints;
    }
}

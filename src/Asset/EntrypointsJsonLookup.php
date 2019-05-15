<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;


use Symfony\Component\DependencyInjection\Container;

class EntrypointsJsonLookup
{

    public function mergeEntries(array $entrypointsJsons, array $entries, string $babelPolyfillEntryName = null) : array
    {
        foreach ($entrypointsJsons as $entrypointsJson) {
            $entrypoints = $this->parseEntrypoints($entrypointsJson);

            $entriesMap = [];
            foreach ($entries as $entry) {
                if (!isset($entry['name'])) {
                    continue;
                }

                $entriesMap[$entry['name']] = true;
            }

            foreach ($entrypoints as $name=>$entrypoint) {
                // Ignore the babel-polyfill entry
                if ($name == $babelPolyfillEntryName) continue;

                // Only add entries that not already exist in the symfony config
                if (!isset($entriesMap[$name])) {
                    $newEntry = [
                        'name' => $name,
                        'head' => false,
                    ];

                    if (isset($entrypoint['css'])) {
                        $newEntry['requiresCss'] = true;
                    }

                    $entries[] = $newEntry;
                }
            }
        }

        return $entries;
    }

    public function parseEntrypoints(string $entrypointsJson) : array
    {
        if (!file_exists($entrypointsJson)) {
            throw new \InvalidArgumentException(sprintf('Could not find the entrypoints.json: the file "%s" does not exist.', $entrypointsJson));
        }

        $entriesData = json_decode(file_get_contents($entrypointsJson), true);

        if (null === $entriesData) {
            throw new \InvalidArgumentException(sprintf('Could not decode the "%s" file', $entrypointsJson));
        }

        if (!isset($entriesData['entrypoints'])) {
            throw new \InvalidArgumentException(sprintf('There is no "entrypoints" key in "%s"', $entrypointsJson));
        }

        return $entriesData['entrypoints'];
    }

}
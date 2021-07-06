<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Psr\Cache\CacheItemPoolInterface;

class EntrypointsJsonLookup
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $useCache = false;

    /**
     * EntrypointsJsonLookup constructor.
     */
    public function __construct(array $bundleConfig, CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache;
        if (isset($bundleConfig['encore_cache_enabled'])) {
            $this->useCache = $bundleConfig['encore_cache_enabled'];
        }
    }

    /**
     * @param array       $entrypointJsonFiles    entrypoint json files
     * @param array       $bundleConfigEntries    Entries defined by encore bundle config
     * @param string|null $babelPolyfillEntryName entry name of babel polyfill
     */
    public function mergeEntries(array $entrypointJsonFiles, array $bundleConfigEntries, LayoutModel $layout = null): array
    {
        foreach ($entrypointJsonFiles as $entrypointsJson) {
            $entrypoints = $this->parseEntrypoints($entrypointsJson);

            $entriesMap = [];
            foreach ($bundleConfigEntries as $entry) {
                if (!isset($entry['name'])) {
                    continue;
                }
                $entriesMap[$entry['name']] = true;
            }

            foreach ($entrypoints as $name => $entrypoint) {
                // Only add entries that not already exist in the symfony config
                if (!isset($entriesMap[$name])) {
                    $newEntry = [
                        'name' => $name,
                        'head' => false,
                    ];

                    if (isset($entrypoint['css'])) {
                        $newEntry['requires_css'] = true;
                    }

                    $bundleConfigEntries[] = $newEntry;
                }
            }
        }

        return $bundleConfigEntries;
    }

    public function parseEntrypoints(string $entrypointsJson): array
    {
        $cached = null;
        if ($this->cache && $this->useCache) {
            // '_default' is the default cache key for single encore builds
            $cached = $this->cache->getItem('_default');

            if ($cached->isHit()) {
                $entriesData = $cached->get();

                return $entriesData['entrypoints'];
            }
        }

        if (!file_exists($entrypointsJson)) {
            throw new \InvalidArgumentException(sprintf('Could not find the entrypoints.json: the file "%s" does not exist. Maybe you forgot to run encore command?', $entrypointsJson));
        }

        $entriesData = json_decode(file_get_contents($entrypointsJson), true);

        if (null === $entriesData) {
            throw new \InvalidArgumentException(sprintf('Could not decode the "%s" file', $entrypointsJson));
        }

        if (!isset($entriesData['entrypoints'])) {
            throw new \InvalidArgumentException(sprintf('There is no "entrypoints" key in "%s"', $entrypointsJson));
        }

        if ($this->useCache && null !== $cached && !$cached->isHit()) {
            $this->cache->save($cached->set($entriesData));
        }

        return $entriesData['entrypoints'];
    }
}

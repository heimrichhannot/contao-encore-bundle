<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Collection;

use Contao\LayoutModel;
use HeimrichHannot\EncoreBundle\Exception\NoEntrypointsException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class EntryCollection
{
    private ConfigurationCollection $configurationCollection;
    private array                   $bundleConfig;
    private bool                    $useCache = false;
    private array                   $entries;
    private CacheItemPoolInterface $cache;

    public function __construct(ConfigurationCollection $configurationCollection, array $bundleConfig, CacheItemPoolInterface $cache)
    {
        $this->configurationCollection = $configurationCollection;
        $this->bundleConfig = $bundleConfig;

        if ($bundleConfig['encore_cache_enabled'] ?? false) {
            $this->useCache = true;
        }
        $this->cache = $cache;
    }

    /**
     * Return all encore entries (from webpack config and registered via bundle).
     * @throws NoEntrypointsException
     */
    public function getEntries(): array
    {
        if (!isset($this->entries)) {
            $this->entries = $this->mergeEntries(
                ($this->bundleConfig['entrypoints_jsons'] ?? []),
                $this->configurationCollection->getJsEntries(['array' => true])
            );
        }

        return $this->entries;
    }

    /**
     * @param array $entrypointJsonFiles entrypoint json files
     * @param array $bundleConfigEntries Entries defined by encore bundle config
     *
     * @throws NoEntrypointsException
     */
    private function mergeEntries(array $entrypointJsonFiles, array $bundleConfigEntries, LayoutModel $layout = null): array
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

    private function parseEntrypoints(string $entrypointsJson): array
    {
        $cached = null;
        if ($this->useCache) {
            // '_default' is the default cache key for single encore builds
            $cached = $this->cache->getItem('_default');

            if ($cached->isHit()) {
                $entriesData = $cached->get();

                return $entriesData['entrypoints'];
            }
        }

        if (!file_exists($entrypointsJson)) {
            throw new NoEntrypointsException(sprintf('Could not find the entrypoints.json: the file "%s" does not exist. Maybe you forgot to run encore command?', $entrypointsJson));
        }

        $entriesData = json_decode(file_get_contents($entrypointsJson), true);

        if (null === $entriesData) {
            throw new NoEntrypointsException(sprintf('Could not decode the "%s" file', $entrypointsJson));
        }

        if (!isset($entriesData['entrypoints'])) {
            throw new NoEntrypointsException(sprintf('There is no "entrypoints" key in "%s"', $entrypointsJson));
        }

        if ($this->useCache) {
            $this->cache->save($cached->set($entriesData));
        }

        return $entriesData['entrypoints'];
    }
}

<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     *
     * @param ContainerInterface          $container
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct(ContainerInterface $container, CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache;

        if ($container->hasParameter('huh.encore')) {
            $config = $container->getParameter('huh.encore');
            if (isset($config['encore']['encoreCacheEnabled'])) {
                $this->useCache = $config['encore']['encoreCacheEnabled'];
            }
        }
    }

    /**
     * @param array       $entrypointsJsons
     * @param array       $entries
     * @param string|null $babelPolyfillEntryName
     *
     * @return array
     */
    public function mergeEntries(array $entrypointsJsons, array $entries, string $babelPolyfillEntryName = null, LayoutModel $layout = null): array
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

            foreach ($entrypoints as $name => $entrypoint) {
                // Be backward compatible
                if ($layout->addEncoreBabelPolyfill && $name == $babelPolyfillEntryName)
                {
                    $entries = array_merge([['name' => $babelPolyfillEntryName, 'head' => false]], $entries);
                }

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
            throw new \InvalidArgumentException(sprintf('Could not find the entrypoints.json: the file "%s" does not exist.', $entrypointsJson));
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

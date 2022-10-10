<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreEntry;

class GlobalContaoAsset
{
    private array               $bundleConfig;
    private ExtensionCollection $extensionCollection;

    public function __construct(array $bundleConfig, ExtensionCollection $extensionCollection)
    {
        $this->bundleConfig = $bundleConfig;
        $this->extensionCollection = $extensionCollection;
    }

    public function cleanGlobalArrayFromConfiguration(): void
    {
        $this->cleanJsAssets($this->bundleConfig['unset_global_keys']['js'] ?? []);
        $this->cleanJqueryAssets($this->bundleConfig['unset_global_keys']['jquery'] ?? []);
        $this->cleanCssAssets($this->bundleConfig['unset_global_keys']['css'] ?? []);

        foreach ($this->extensionCollection->getExtensions() as $extension) {
            foreach ($extension->getEntries() as $entry) {
                foreach ($entry->getReplaceGlobelKeys() as $key => $value) {
                    $this->cleanFromGlobalArray($key, $value);
                }
            }
        }

        if (true === ($this->bundleConfig['unset_jquery'] ?? false)) {
            $this->removeJqueryAsset();
        }
    }

    public function cleanFromGlobalArray(string $key, array $entries): void
    {
        if (!\in_array($key, EncoreEntry::GLOBAL_KEYS, true)) {
            throw new \InvalidArgumentException("Global asset key $key is not supported!");
        }

        if (!isset($GLOBALS[$key]) || !\is_array($GLOBALS[$key])) {
            return;
        }

        foreach ($entries as $entry) {
            if (isset($GLOBALS[$key][$entry])) {
                unset($GLOBALS[$key][$entry]);
            }
        }
    }

    public function cleanJsAssets(array $entries): void
    {
        $this->cleanFromGlobalArray('TL_JAVASCRIPT', $entries);
    }

    public function cleanJqueryAssets(array $entries): void
    {
        $this->cleanFromGlobalArray('TL_JQUERY', $entries);
    }

    public function cleanCssAssets(array $entries): void
    {
        $this->cleanFromGlobalArray('TL_USER_CSS', $entries);
        $this->cleanFromGlobalArray('TL_CSS', $entries);
    }

    public function removeJqueryAsset(): void
    {
        if (false !== ($key = array_search('assets/jquery/js/jquery.min.js|static', $GLOBALS['TL_JAVASCRIPT'], true))) {
            unset($GLOBALS['TL_JAVASCRIPT'][$key]);
        }
    }
}

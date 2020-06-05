<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

class FrontendAsset
{
    /**
     * @var array
     */
    private $activeEntrypoints = [];

    /**
     * Add an active entrypoint.
     */
    public function addActiveEntrypoint(string $entrypoint)
    {
        $this->activeEntrypoints[] = $entrypoint;
    }

    /**
     * Return a list of all active entrypoints.
     *
     * @return array
     */
    public function getActiveEntrypoints()
    {
        return $this->activeEntrypoints;
    }

    /**
     * Check if an entrypoint is set as active entrypoint.
     *
     * @return bool
     */
    public function isActiveEntrypoint(string $entrypoint)
    {
        return \in_array($entrypoint, $this->activeEntrypoints, true);
    }
}

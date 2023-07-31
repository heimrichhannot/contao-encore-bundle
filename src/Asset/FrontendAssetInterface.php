<?php

namespace HeimrichHannot\EncoreBundle\Asset;

interface FrontendAssetInterface
{
    /**
     * Add an active entrypoint.
     */
    public function addActiveEntrypoint(string $entrypoint);

    /**
     * Return a list of all active entrypoints.
     *
     * @return array
     */
    public function getActiveEntrypoints();

    /**
     * Check if an entrypoint is set as active entrypoint.
     *
     * @return bool
     */
    public function isActiveEntrypoint(string $entrypoint);
}
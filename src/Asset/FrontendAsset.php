<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Asset;


class FrontendAsset
{
    /**
     * @var array
     */
    private $activeEntrypoints = [];

    /**
     * Add an active entrypoint
     *
     * @param string $entrypoint
     */
    public function addActiveEntrypoint(string $entrypoint)
    {
        $this->activeEntrypoints[] = $entrypoint;
    }

    /**
     * Return a list of all active entrypoints
     *
     * @return array
     */
    public function getActiveEntrypoints()
    {
        return $this->activeEntrypoints;
    }

    /**
     * Check if an entrypoint is set as active entrypoint
     *
     * @param string $entrypoint
     * @return bool
     */
    public function isActiveEntrypoint(string $entrypoint)
    {
        return in_array($entrypoint, $this->activeEntrypoints);

    }
}
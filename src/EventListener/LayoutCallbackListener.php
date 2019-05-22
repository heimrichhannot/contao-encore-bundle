<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\EventListener;


use Contao\DataContainer;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class LayoutCallbackListener
{
    /**
     * @var TagAwareAdapter
     */
    private $cache;

    public function __construct(TagAwareAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param DataContainer $dc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onSubmitCallback($dc)
    {
        $this->cache->invalidateTags(['layout_'.$dc->id]);
        return;
    }
}
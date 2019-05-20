<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\DataContainer;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntryChoice extends AbstractChoice
{
    /**
     * @var EntrypointsJsonLookup
     */
    private $entrypointsJsonLookup;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, EntrypointsJsonLookup $entrypointsJsonLookup)
    {
        parent::__construct($container->get('contao.framework'));
        $this->entrypointsJsonLookup = $entrypointsJsonLookup;
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = $this->container->getParameter('huh.encore');

        // add entries from the entrypoints.json
        if (isset($config['encore']['entrypointsJsons']) && is_array($config['encore']['entrypointsJsons']) && !empty($config['encore']['entrypointsJsons'])) {
            if (!isset($config['encore']['entries'])) {
                $config['encore']['entries'] = [];
            } else if (!is_array($config['encore']['entries'])) {
                return $choices;
            }

            $dc = $this->getContext();
            $isBabelPolyfillAdded = ($dc instanceof DataContainer && $dc->activeRecord != null && $dc->activeRecord->addEncoreBabelPolyfill);

            $config['encore']['entries'] = $this->entrypointsJsonLookup->mergeEntries(
                $config['encore']['entrypointsJsons'],
                $config['encore']['entries'],
                $isBabelPolyfillAdded ? $dc->activeRecord->encoreBabelPolyfillEntryName : null);
        }

        if (!isset($config['encore']['entries'])) {
            return $choices;
        }

        foreach ($config['encore']['entries'] as $entry) {
            $choices[$entry['name']] = $entry['name'] . (isset($entry['file']) ? ' [' . $entry['file'] . ']' : '');
        }

        asort($choices);

        return $choices;
    }
}

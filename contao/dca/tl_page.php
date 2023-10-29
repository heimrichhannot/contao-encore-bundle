<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;

$dca = &$GLOBALS['TL_DCA']['tl_page'];

EncoreEntriesSelectField::register('tl_page')
    ->setIncludeActiveCheckbox(true);

$pm = PaletteManipulator::create()
    ->addField('encoreEntries', 'layout_legend', PaletteManipulator::POSITION_APPEND);

foreach (array_keys($dca['palettes']) as $palette) {
    if ('__selector__' === $palette || empty($palette) || !is_string($palette)) {
        continue;
    }
    $pm->applyToPalette($palette, 'tl_page');
}
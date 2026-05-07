<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DataContainer\PaletteNotFoundException;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;

EncoreEntriesSelectField::register('tl_layout')
    ->setIncludeActiveCheckbox(true);

$dca = &$GLOBALS['TL_DCA']['tl_layout'];

/*
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'addEncore';

$pm = PaletteManipulator::create()
    ->addLegend('encore_legend', 'modules_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('addEncore', 'encore_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_layout');

// BC for contao < 5.6
try {
    $pm->applyToPalette('modern', 'tl_layout');
} catch (PaletteNotFoundException) {
}



/*
 * Subpalettes
 */
$dca['subpalettes']['addEncore'] = EncoreEntriesSelectField::NAME_DEFAULT . ',encoreStylesheetsImportsTemplate,encoreScriptsImportsTemplate';

/**
 * Fields.
 */
$fields = [
    'addEncore' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['addEncore'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => [
            'tl_class' => 'w50',
            'submitOnChange' => true,
        ],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'encoreStylesheetsImportsTemplate' => [
        'exclude' => true,
        'inputType' => 'select',
        'eval' => [
            'tl_class' => 'w50 clr',
            'includeBlankOption' => true,
        ],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
    'encoreScriptsImportsTemplate' => [
        'exclude' => true,
        'inputType' => 'select',
        'eval' => [
            'tl_class' => 'w50',
            'includeBlankOption' => true,
        ],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

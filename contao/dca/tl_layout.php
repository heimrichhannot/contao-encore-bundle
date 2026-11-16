<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;

EncoreEntriesSelectField::register('tl_layout')
    ->setIncludeActiveCheckbox(true);

$dca = &$GLOBALS['TL_DCA']['tl_layout'];

/*
 * Palettes
 */
$pm = PaletteManipulator::create()
    ->addLegend('encore_legend', 'modules_legend', PaletteManipulator::POSITION_AFTER)
    ->addField(['addEncore', EncoreEntriesSelectField::NAME_DEFAULT, 'encoreStylesheetsImportsTemplate', 'encoreScriptsImportsTemplate'], 'encore_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_layout');

// BC for contao < 5.6
if (isset($dca['palettes']['modern'])) {
    $pm->applyToPalette('modern', 'tl_layout');
}

/**
 * Fields.
 */
$fields = [
    'addEncore' => [
        'exclude' => true,
        'inputType' => 'select',
        'default' => 'i',
        'options' => ['i', '1', '0'],
        'reference' => &$GLOBALS['TL_LANG']['tl_layout']['encoreActivationOptions'],
        'eval' => [
            'tl_class' => 'w50',
        ],
        'sql' => [
            'type' => 'string',
            'length' => 1,
            'fixed' => true,
            'default' => 'i',
        ],
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

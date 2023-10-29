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
$dca['palettes']['__selector__'][] = 'addEncore';

PaletteManipulator::create()
    ->addLegend('encore_legend', 'jquery_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('addEncore', 'encore_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_layout');

/*
 * Subpalettes
 */
$dca['subpalettes']['addEncore'] = 'encoreEntries,encoreStylesheetsImportsTemplate,encoreScriptsImportsTemplate';

/**
 * Fields.
 */
$fields = [
    'addEncore' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['addEncore'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'encoreStylesheetsImportsTemplate' => [
        'exclude' => true,
        'inputType' => 'select',
        'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
    'encoreScriptsImportsTemplate' => [
        'exclude' => true,
        'inputType' => 'select',
        'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

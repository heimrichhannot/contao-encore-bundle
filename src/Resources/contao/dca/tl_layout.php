<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;

$dca = &$GLOBALS['TL_DCA']['tl_layout'];

/*
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'addEncore';

$dca['palettes']['default'] = str_replace('{jquery_legend', '{encore_legend},addEncore;{jquery_legend', $dca['palettes']['default']);

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
    'encoreEntries' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['encoreEntries'],
        'exclude' => true,
        'inputType' => 'multiColumnEditor',
        'eval' => [
            'tl_class' => 'long clr',
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'sortable' => true,
                'fields' => [
                    'entry' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_layout']['encoreEntries_entry'],
                        'exclude' => true,
                        'filter' => true,
                        'inputType' => 'select',
                        'options_callback' => [EncoreEntryOptionListener::class, 'getEntriesAsOptions'],
                        'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'groupStyle' => 'width: 710px', 'chosen' => true],
                    ],
                ],
            ],
        ],
        'sql' => 'blob NULL',
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
        'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

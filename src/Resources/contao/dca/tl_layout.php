<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;

$dca = &$GLOBALS['TL_DCA']['tl_layout'];

/*
 * Config
 */

$dca['config']['onload_callback'][] = [\HeimrichHannot\EncoreBundle\DataContainer\LayoutContainer::class, 'onLoadCallback'];

/*
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'addEncore';
$dca['palettes']['__selector__'][] = 'addEncoreBabelPolyfill';

$dca['palettes']['default'] = str_replace('{jquery_legend', '{encore_legend},addEncore;{jquery_legend', $dca['palettes']['default']);

/*
 * Subpalettes
 */
$dca['subpalettes']['addEncore'] = 'addEncoreBabelPolyfill,encoreEntries,encoreStylesheetsImportsTemplate,encoreScriptsImportsTemplate';
$dca['subpalettes']['addEncoreBabelPolyfill'] = 'encoreBabelPolyfillEntryName';

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
    'addEncoreBabelPolyfill' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['addEncoreBabelPolyfill'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'encoreBabelPolyfillEntryName' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['encoreBabelPolyfillEntryName'],
        'exclude' => true,
        'search' => true,
        'inputType' => 'text',
        'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
        'sql' => "varchar(255) NOT NULL default 'babel-polyfill'",
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
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['encoreStylesheetsImportsTemplate'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['huh.encore.choice.template.imports', 'getCachedChoices'],
        'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
    'encoreScriptsImportsTemplate' => [
        'label' => &$GLOBALS['TL_LANG']['tl_layout']['encoreScriptsImportsTemplate'],
        'exclude' => true,
        'inputType' => 'select',
        'options_callback' => ['huh.encore.choice.template.imports', 'getCachedChoices'],
        'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

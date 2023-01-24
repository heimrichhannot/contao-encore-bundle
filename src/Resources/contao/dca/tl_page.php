<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/*
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'addEncore';
$dca['palettes']['__selector__'][] = 'addEncoreBabelPolyfill';

foreach (array_keys($dca['palettes']) as $palette) {
    $dca['palettes'][$palette] = str_replace(';{layout_legend', ';{encore_legend},encoreEntries;{layout_legend', $dca['palettes'][$palette]);
}

/**
 * Fields.
 */
$fields = [
    'encoreEntries' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries'],
        'exclude' => true,
        'inputType' => 'multiColumnEditor',
        'eval' => [
            'tl_class' => 'long clr',
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'sortable' => true,
                'fields' => [
                    'active' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries_active'],
                        'exclude' => true,
                        'default' => true,
                        'inputType' => 'checkbox',
                        'eval' => ['tl_class' => 'w50', 'groupStyle' => 'width: 65px'],
                    ],
                    'entry' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries_entry'],
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
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

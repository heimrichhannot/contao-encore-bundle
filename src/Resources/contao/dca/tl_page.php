<?php

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/**
 * Palettes
 */
$dca['palettes']['__selector__'][] = 'addEncore';
$dca['palettes']['__selector__'][] = 'addEncoreSharedEntry';
$dca['palettes']['__selector__'][] = 'addEncoreBabelPolyfill';

foreach (['regular', 'root'] as $palette) {
    $dca['palettes'][$palette] = str_replace(';{layout_legend', ';{encore_legend},encoreEntries;{layout_legend', $dca['palettes'][$palette]);
}

$dca['palettes']['root'] = str_replace('encoreEntries', 'addEncore', $dca['palettes']['root']);

/**
 * Subpalettes
 */
$dca['subpalettes']['addEncore']              = 'encorePublicPath,addEncoreSharedEntry,addDynamicEncoreImports,addEncoreBabelPolyfill,encoreEntries,encoreStylesheetsImportsTemplate,encoreScriptsImportsTemplate';
$dca['subpalettes']['addEncoreSharedEntry']   = 'encoreSharedEntryName';
$dca['subpalettes']['addEncoreBabelPolyfill'] = 'encoreBabelPolyfillEntryName';

/**
 * Fields
 */
$fields = [
    'addEncore'                        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addEncore'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'encorePublicPath'                 => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['encorePublicPath'],
        'exclude'   => true,
        'search'    => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
        'sql'       => "varchar(255) NOT NULL default ''"
    ],
    'addEncoreSharedEntry'             => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addEncoreSharedEntry'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'encoreSharedEntryName'            => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['encoreSharedEntryName'],
        'exclude'   => true,
        'search'    => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
        'sql'       => "varchar(255) NOT NULL default ''"
    ],
    'addDynamicEncoreImports'          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addDynamicEncoreImports'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'addEncoreBabelPolyfill'           => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addEncoreBabelPolyfill'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'encoreBabelPolyfillEntryName'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['encoreBabelPolyfillEntryName'],
        'exclude'   => true,
        'search'    => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
        'sql'       => "varchar(255) NOT NULL default ''"
    ],
    'encoreEntries'                    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'tl_class'          => 'long clr',
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'sortable'    => true,
                'fields'      => [
                    'active' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries_active'],
                        'exclude'   => true,
                        'default'   => true,
                        'inputType' => 'checkbox',
                        'eval'      => ['tl_class' => 'w50']
                    ],
                    'entry'  => [
                        'label'            => &$GLOBALS['TL_LANG']['tl_page']['encoreEntries_entry'],
                        'exclude'          => true,
                        'filter'           => true,
                        'inputType'        => 'select',
                        'options_callback' => ['huh.encore.choice.entry', 'getCachedChoices'],
                        'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true]
                    ]
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ],
    'encoreStylesheetsImportsTemplate' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_page']['encoreStylesheetsImportsTemplate'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.encore.choice.template.imports', 'getCachedChoices'],
        'eval'             => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "varchar(128) NOT NULL default ''",
    ],
    'encoreScriptsImportsTemplate' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_page']['encoreScriptsImportsTemplate'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.encore.choice.template.imports', 'getCachedChoices'],
        'eval'             => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "varchar(128) NOT NULL default ''",
    ],
];

$dca['fields'] += $fields;
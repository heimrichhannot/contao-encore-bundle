<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_layout'];

/*
 * Fields
 */
$lang['addEncore'][0] = 'Activate Webpack Encore';
$lang['addEncore'][1] = 'Choose this option if you want to activate Encore support.';
$lang['encoreEntries'][0] = 'Active Entries';
$lang['encoreEntries'][1] =
    'Select webpack entries that should be rendered on this and all inheriting pages. You can overwrite this setting on inheriting pages due page inheritance.';
$lang['encoreEntries_entry'][0] = 'Entry';
$lang['encoreEntries_active'][0] = 'Active';
$lang['encoreStylesheetsImportsTemplate'][0] = 'Stylesheet import template';
$lang['encoreStylesheetsImportsTemplate'][1] = 'Choose an custom stylesheet import template.';
$lang['encoreScriptsImportsTemplate'][0] = 'Javascript import template';
$lang['encoreScriptsImportsTemplate'][1] = 'Choose a custom javascript import template.';

/*
 * Legends
 */
$lang['encore_legend'] = 'Encore';

/*
 * Info
 */
$lang['INFO']['jquery_order_conflict'] = '"Include jQuery" is activated in layout. This may lead to asset order conflicts, cause encore entries are loaded before contao assets (including jQuery). We recommend to add jQuery with encore (you\'ll find support in the <u><a href="https://github.com/heimrichhannot/contao-encore-bundle" target="_blank">encore bundle documentation</a></u>) and disabled "Include jQuery" in the layout settings. If you need jQuery template, you could also use the <i>unset_jquery</i> configuration of encore bundle.';

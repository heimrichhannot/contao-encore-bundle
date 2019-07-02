<?php

$lang = &$GLOBALS['TL_LANG']['tl_layout'];

/**
 * Fields
 */
$lang['addEncore'][0]                        = 'Activate Webpack Encore';
$lang['addEncore'][1]                        = 'Choose this option if you want to activate Encore support.';
$lang['addEncoreBabelPolyfill'][0]           = 'Deprecated: Add babel polyfill';
$lang['addEncoreBabelPolyfill'][1]           = 'This option is deprecated and should not be used anymore. Just add babel polyfill as active entry. ';
$lang['encoreBabelPolyfillEntryName'][0]     = 'babel-polyfill-Entry-Name';
$lang['encoreBabelPolyfillEntryName'][1]     =
    'Enter the name of babel-polyfill-Entry as set in Encore.addEntry() as first parameter.';
$lang['encoreEntries'][0]                    = 'Active Entries';
$lang['encoreEntries'][1]                    =
    'Select webpack entries that should be rendered on this and all inheriting pages. You can overwrite this setting on inheriting pages due page inheritance.';
$lang['encoreEntries_entry'][0]              = 'Entries';
$lang['encoreEntries_active'][0]             = 'Active';
$lang['encoreStylesheetsImportsTemplate'][0] = 'Stylesheet import template';
$lang['encoreStylesheetsImportsTemplate'][1] = 'Choose an custom stylesheet import template.';
$lang['encoreScriptsImportsTemplate'][0]     = 'Javascript import template';
$lang['encoreScriptsImportsTemplate'][1]     = 'Choose a custom javascript import template.';

/**
 * Legends
 */
$lang['encore_legend'] = 'Encore';
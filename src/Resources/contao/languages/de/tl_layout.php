<?php

$lang = &$GLOBALS['TL_LANG']['tl_layout'];

/**
 * Fields
 */
$lang['addEncore'][0]                        = 'Webpack Encore aktivieren';
$lang['addEncore'][1]                        = 'Wählen Sie diese Option, wenn Sie die Encore-Unterstützung aktivieren möchten.';
$lang['addEncoreBabelPolyfill'][0]           = 'Veraltet: babel-polyfill hinzufügen';
$lang['addEncoreBabelPolyfill'][1]           = 'Diese Option ist veraltet und sollte nicht mehr verwendet werden. Das Babel-Polyfill kann einfach als aktives Entry hinzugefügt werden. ';
$lang['encoreBabelPolyfillEntryName'][0]     = 'babel-polyfill-Entry-Name';
$lang['encoreBabelPolyfillEntryName'][1]     =
    'Geben Sie hier den Namen des babel-polyfill-Entry ein, wie er Encore.addEntry() als erster Parameter übergeben wird.';
$lang['encoreEntries'][0]                    = 'Aktive Entries';
$lang['encoreEntries'][1]                    =
    'Legen Sie hier fest, welche webpack-Entrys auf welchen Seiten gerendert werden sollen. Sie können diese Festlegungen auf eventuellen Unterseiten mit Seitenvererbung überschreiben.';
$lang['encoreEntries_entry'][0]              = 'Entrys';
$lang['encoreEntries_active'][0]             = 'Aktiv';
$lang['encoreStylesheetsImportsTemplate'][0] = 'Alternatives Stylesheets Import-Template';
$lang['encoreStylesheetsImportsTemplate'][1] = 'Wählen Sie hier bei Bedarf ein alternatives Import-Template für Stylesheets aus.';
$lang['encoreScriptsImportsTemplate'][0]     = 'Alternatives Javascript Import-Template';
$lang['encoreScriptsImportsTemplate'][1]     = 'Wählen Sie hier bei Bedarf ein alternatives Import-Template für Javascript aus.';

/**
 * Legends
 */
$lang['encore_legend'] = 'Encore';
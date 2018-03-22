<?php

$lang = &$GLOBALS['TL_LANG']['tl_page'];

/**
 * Fields
 */
$lang['addEncore'][0]                    = 'Webpack Encore aktivieren';
$lang['addEncore'][1]                    = 'Wählen Sie diese Option, wenn Sie die Encore-Unterstützung aktivieren möchten.';
$lang['encorePublicPath'][0]             = 'Öffentlicher Pfad';
$lang['encorePublicPath'][1]             =
    'Geben Sie hier ein Verzeichnis relativ zum /web-Verzeichnis aus, in dem die von Encore generierten Asset-Dateien gespeichert werden (Beispiel: build).';
$lang['addEncoreSharedEntry'][0]         = 'Shared Entry hinzufügen';
$lang['addEncoreSharedEntry'][1]         = 'Wählen Sie diese Option, wenn Sie in Ihrer webpack.config.js einen Shared Entry definiert haben.';
$lang['encoreSharedEntryName'][0]        = 'Shared Entry-Name';
$lang['encoreSharedEntryName'][1]        =
    'Geben Sie hier den Namen des Shared Entry ein, wie er Encore.createSharedEntry() als erster Parameter übergeben wird.';
$lang['addDynamicEncoreImports'][0]      = 'Dynamische Importe aktivieren';
$lang['addDynamicEncoreImports'][1]      =
    'Wählen Sie diese Option, wenn die JavaScript-Dateien, die Sie einbinden, dynamische Importe mittels des webpack-Operators "import()" enthalten.';
$lang['addEncoreBabelPolyfill'][0]       = 'babel-polyfill hinzufügen (für IE <= 11)';
$lang['addEncoreBabelPolyfill'][1]       =
    'Wählen Sie diese Option, wenn der Internet Explorer in Version <= 11 unterstützt werden soll. In modernen Browsern (auch Edge) wird das Polyfill, das u.a. Promises bereitstellt, in der Regel nicht benötigt.';
$lang['encoreBabelPolyfillEntryName'][0] = 'babel-polyfill-Entry-Name';
$lang['encoreBabelPolyfillEntryName'][1] =
    'Geben Sie hier den Namen des babel-polyfill-Entry ein, wie er Encore.addEntry() als erster Parameter übergeben wird.';
$lang['encoreEntries'][0]                = 'Aktive Entrys';
$lang['encoreEntries'][1]                =
    'Legen Sie hier fest, welche webpack-Entrys auf welchen Seiten gerendert werden sollen. Sie können diese Festlegungen auf eventuellen Unterseiten mit Seitenvererbung überschreiben.';
$lang['encoreEntries_entry'][0]          = 'Entrys';
$lang['encoreEntries_active'][0]         = 'Aktiv';
$lang['encoreImportsTemplate'][0]        = 'Alternatives Import-Template';
$lang['encoreImportsTemplate'][1]        = 'Wählen Sie hier bei Bedarf ein alternatives Import-Template aus.';

/**
 * Legends
 */
$lang['encore_legend'] = 'Encore';
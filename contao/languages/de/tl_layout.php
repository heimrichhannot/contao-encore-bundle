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
$lang['addEncore'][0] = 'Webpack Encore aktivieren';
$lang['addEncore'][1] = 'Wählen Sie diese Option, wenn Sie die Encore-Unterstützung aktivieren möchten.';
$lang['encoreEntries'][0] = 'Aktive Entries';
$lang['encoreEntries'][1] =
    'Legen Sie hier fest, welche webpack-Entrys auf welchen Seiten gerendert werden sollen. Sie können diese Festlegungen auf eventuellen Unterseiten mit Seitenvererbung überschreiben.';
$lang['encoreEntries_entry'][0] = 'Entry';
$lang['encoreEntries_active'][0] = 'Aktiv';
$lang['encoreStylesheetsImportsTemplate'][0] = 'Alternatives Stylesheets Import-Template';
$lang['encoreStylesheetsImportsTemplate'][1] = 'Wählen Sie hier bei Bedarf ein alternatives Import-Template für Stylesheets aus.';
$lang['encoreScriptsImportsTemplate'][0] = 'Alternatives Javascript Import-Template';
$lang['encoreScriptsImportsTemplate'][1] = 'Wählen Sie hier bei Bedarf ein alternatives Import-Template für Javascript aus.';

/*
 * Legends
 */
$lang['encore_legend'] = 'Encore';

/*
 * Info
 */
$lang['INFO']['jquery_order_conflict'] = 'Sie haben "jQuery laden" im Layout aktiviert. Dies kann zu einem Reihenfolge-Konflikt führen, da Encore-Entries vor den Assets von Contao (inklusive jQuery) geladen werden. Wir empfehlen Ihnen, jQuery über Encore einzubinden (Sie finden Unterstützung dazu in der <u><a href="https://github.com/heimrichhannot/contao-encore-bundle" target="_blank">Dokumentation des Encore-Bundles</a></u>) und "jQuery laden" in den Layouteinstellungen zu deaktivieren. Wenn Sie jQuery-Templates benötigen, können Sie auch die <i>unset_jquery</i>-Konfiguration des Encore-Bundles nutzen.';

<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;

EncoreEntriesSelectField::register('tl_theme');

$GLOBALS['TL_DCA']['tl_theme']['palettes']['__selector__'][] = 'addEncore';
$GLOBALS['TL_DCA']['tl_theme']['subpalettes']['addEncore'] = EncoreEntriesSelectField::NAME_DEFAULT;

PaletteManipulator::create()
    ->addLegend('encore_legend', 'config_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('addEncore', 'encore_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_theme');

$GLOBALS['TL_DCA']['tl_theme']['fields']['addEncore'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50',
        'submitOnChange' => true,
    ],
    'sql' => [
        'type' => 'string',
        'length' => 1,
        'fixed' => true,
        'default' => '',
    ],
];

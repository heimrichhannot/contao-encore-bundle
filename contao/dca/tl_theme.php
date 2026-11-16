<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;

EncoreEntriesSelectField::register('tl_theme');

PaletteManipulator::create()
    ->addLegend('encore_legend', 'config_legend', PaletteManipulator::POSITION_AFTER)
    ->addField(['addEncore', EncoreEntriesSelectField::NAME_DEFAULT], 'encore_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_theme');

$GLOBALS['TL_DCA']['tl_theme']['fields']['addEncore'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => 'string',
        'length' => 1,
        'fixed' => true,
        'default' => '',
    ],
];

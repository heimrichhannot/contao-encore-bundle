<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Dca;

use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;
use Symfony\Contracts\Translation\TranslatorInterface;

class DcaGenerator
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * DcaGenerator constructor.
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getEncoreEntriesSelect(bool $includeActiveCheckbox = false): array
    {
        $field = [
            'label' => $this->getLabel('encoreEntriesSelect'),
            'exclude' => true,
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'sortable' => true,
                    'fields' => [
                        'entry' => [
                            'label' => $this->getLabel('encoreEntriesSelect_entry'),
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
        ];
        if ($includeActiveCheckbox) {
            $field['eval']['multiColumnEditor']['fields'] = array_merge(
                [
                    'active' => [
                        'label' => $this->getLabel('encoreEntriesSelect_active'),
                        'exclude' => true,
                        'default' => true,
                        'inputType' => 'checkbox',
                        'eval' => ['tl_class' => 'w50', 'groupStyle' => 'width: 65px'],
                    ],
                ],
                $field['eval']['multiColumnEditor']['fields']
            );
        }

        return $field;
    }

    public function getLabel(string $field): array
    {
        return [
            $this->translator->trans('huh.encore.fields.'.$field.'.name'),
            $this->translator->trans('huh.encore.fields.'.$field.'.description'),
        ];
    }
}

<?php

namespace HeimrichHannot\EncoreBundle\EventListener\DcaField;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\EncoreBundle\EventListener\Callback\EncoreEntryOptionListener;
use Symfony\Contracts\Translation\TranslatorInterface;

class EncoreEntriesSelectFieldListener
{
    public function __construct(
        protected TranslatorInterface $translator,
    ) {}

    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if (!isset(EncoreEntriesSelectField::getRegistrations()[$table])) {
            return;
        }

        $options = EncoreEntriesSelectField::getRegistrations()[$table];

        $field = [
            'label' => $options->getFieldLabel() ?? $this->getLabel('encoreEntriesSelect'),
            'exclude' => true,
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'sortable' => true,
                    'fields' => [
                        'entry' => [
                            'label' => $options->getSelectLabel() ?? $this->getLabel('encoreEntriesSelect_entry'),
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
        if ($options->isIncludeActiveCheckbox()) {
            $field['eval']['multiColumnEditor']['fields'] = array_merge(
                [
                    'active' => [
                        'label' => $options->getCheckboxLabel() ?? $this->getLabel('encoreEntriesSelect_active'),
                        'exclude' => true,
                        'default' => true,
                        'inputType' => 'checkbox',
                        'eval' => ['tl_class' => 'w50', 'groupStyle' => 'width: 70px;align-self: center;'],
                    ],
                ],
                $field['eval']['multiColumnEditor']['fields']
            );
        }

        $GLOBALS['TL_DCA'][$table]['fields'][$options->getFieldName()] = $field;
    }

    public function getLabel(string $field): array
    {
        return [
            $this->translator->trans('huh.encore.fields.'.$field.'.name'),
            $this->translator->trans('huh.encore.fields.'.$field.'.description'),
        ];
    }
}
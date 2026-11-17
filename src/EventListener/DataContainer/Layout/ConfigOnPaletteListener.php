<?php

namespace HeimrichHannot\EncoreBundle\EventListener\DataContainer\Layout;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_layout', target: 'config.onpalette')]
class ConfigOnPaletteListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(string $palette, DataContainer $dc): string
    {
        $row = $dc->getCurrentRecord();
        if (!$row) {
            return $palette;
        }

        $mode = $row['addEncore'] ?? '';
        $request = $this->requestStack->getCurrentRequest();
        if ($request && 'tl_layout' === $request->request->get('FORM_SUBMIT')) {
            $key = 'editAll' === $request->query->get('act') ? 'addEncore_' . $dc->id : 'addEncore';
            $mode = $request->request->get($key, $mode);
        }

        if ('i' !== $mode || empty($row['pid'])) {
            return $palette;
        }

        $theme = $dc->getCurrentRecord($row['pid'], 'tl_theme');
        if (empty($theme['addEncore'])) {
            return $palette;
        }

        return PaletteManipulator::create()
            ->addField([EncoreEntriesSelectField::NAME_DEFAULT, 'encoreStylesheetsImportsTemplate', 'encoreScriptsImportsTemplate'], 'addEncore', PaletteManipulator::POSITION_AFTER)
            ->applyToString($palette);
    }
}

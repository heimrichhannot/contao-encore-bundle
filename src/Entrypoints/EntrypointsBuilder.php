<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntrypointsBuilder
{
    private ?PageModel $pageModel = null;
    private string $pageField = '';
    private ?LayoutModel $layout = null;
    private string $layoutField = '';
    private array $available = [];

    public function __construct(
        private readonly Utils $utils,
        private readonly FrontendAsset $frontendAsset,
        private readonly EntryCollection $entryCollection,
    ) {}

    public function setPage(PageModel $page, string $field = EncoreEntriesSelectField::NAME_DEFAULT): self
    {
        $this->pageModel = $page;
        $this->pageField = $field;
        return $this;
    }

    public function setLayout(LayoutModel $layout, string $field = EncoreEntriesSelectField::NAME_DEFAULT): self
    {
        $this->layout = $layout;
        $this->layoutField = $field;
        return $this;
    }

    public function build(): Entrypoints
    {
        $entrypoints = new Entrypoints();
        $available = $this->entryCollection->getEntries();
        $available = array_combine(array_column($available, 'name'), $available);
        $this->available = $available;

        foreach ($this->frontendAsset->getActiveEntrypoints() as $entrypoint) {
            $this->addEntrypoint(
                entrypoints: $entrypoints,
                name: $entrypoint,
            );
        }

        if ($this->pageModel && !$this->layout) {
            $this->pageModel->loadDetails();
            $layout = LayoutModel::findByPk($this->pageModel->layout);
            if ($layout) {
                $this->setLayout($layout);
            }
        }

        if ($this->layout) {
            foreach (StringUtil::deserialize($this->layout->{$this->layoutField}, true) as $entrypoint) {
                $this->addEntrypoint(
                    entrypoints: $entrypoints,
                    name: $entrypoint['name'] ?? '',
                    active: (bool)($entrypoint['active'] ?? true)
                );
            }
        }

        if (null !== $this->pageModel) {
            $pages = $this->utils->model()->findParentsRecursively($this->pageModel, 'pid');
            $pages[] = $this->pageModel;

            foreach ($pages as $page) {
                foreach (StringUtil::deserialize($page->{$this->pageField}, true) as $entrypoint) {
                    $this->addEntrypoint(
                        entrypoints: $entrypoints,
                        name: $entrypoint['name'] ?? '',
                        active: (bool)($entrypoint['active'] ?? true)
                    );
                }
            }
        }

        return $entrypoints;
    }

    private function addEntrypoint(Entrypoints $entrypoints, string $name, bool $active = true): void
    {
        if ('' === $name) {
            return;
        }

        if (!isset($this->available[$name])) {
            return;
        }

        $entrypoints->add(Entrypoint::fromArray($this->available[$name], $active));
    }
}
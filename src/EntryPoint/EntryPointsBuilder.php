<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntryPointsBuilder
{
    private ?PageModel $pageModel = null;
    private string $pageField = '';
    private ?LayoutModel $layout = null;
    private string $layoutField = '';
    private ?FrontendAsset $frontendAsset = null;

    private array $available = [];

    public function __construct(
        private readonly Utils $utils,
        private readonly EntryCollection $entryCollection,
    ) {}

    public function setPage(?PageModel $page, string $field = EncoreEntriesSelectField::NAME_DEFAULT): self
    {
        $this->pageModel = $page;
        $this->pageField = $field;
        return $this;
    }

    public function setLayout(?LayoutModel $layout, string $field = EncoreEntriesSelectField::NAME_DEFAULT): self
    {
        $this->layout = $layout;
        $this->layoutField = $field;
        return $this;
    }

    public function setFrontendAsset(?FrontendAsset $frontendAsset): self
    {
        $this->frontendAsset = $frontendAsset;
        return $this;
    }

    public function build(): EntryPoints
    {
        $entryPoints = new EntryPoints();
        $available = $this->entryCollection->getEntries();
        $available = array_combine(array_column($available, 'name'), $available);
        $this->available = $available;

        if ($this->frontendAsset) {
            foreach ($this->frontendAsset->getActiveEntrypoints() as $entryPoint) {
                $this->addEntryPoint(
                    entryPoints: $entryPoints,
                    name: $entryPoint,
                    origin: FrontendAsset::class,
                );
            }
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
                $this->addEntryPoint(
                    entryPoints: $entryPoints,
                    name: $entrypoint['entry'] ?? '',
                    active: (bool)($entrypoint['active'] ?? true),
                    origin: 'tl_layout.'.$this->layout->id,
                    extension: 'App',
                );
            }
        }

        if (null !== $this->pageModel) {
            $pages = $this->utils->model()->findParentsRecursively($this->pageModel, 'pid');
            $pages[] = $this->pageModel;

            foreach ($pages as $page) {
                foreach (StringUtil::deserialize($page->{$this->pageField}, true) as $entrypoint) {
                    $this->addEntryPoint(
                        entryPoints: $entryPoints,
                        name: $entrypoint['entry'] ?? '',
                        active: (bool)($entrypoint['active'] ?? true),
                        origin: 'tl_page.'.$page->id,
                        extension: 'App',
                    );
                }
            }
        }

        return $entryPoints;
    }

    private function addEntryPoint(EntryPoints $entryPoints, string $name, bool $active = true, string $origin = '', string $extension = ''): void
    {
        if ('' === $name) {
            return;
        }

        if (!isset($this->available[$name])) {
            return;
        }

        $entryPoints->add(new EntryPoint(
            name: $name,
            active: $active,
            head: $this->available[$name]['head'] ?? false,
            requiresCss: (bool)($this->available[$name]['requires_css'] ?? true),
            origin: $origin,
            extension: $extension,
        ));
    }
}
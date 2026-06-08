<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\EncoreBundle\Request\ResponseContext\EntryBag;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntryPointsBuilder
{
    private ?PageModel $pageModel = null;
    private string $pageField = '';
    private ?LayoutModel $layout = null;
    private string $layoutField = '';

    private array $available = [];
    private ?ResponseContext $responseContext = null;
    private ?EntryBag $entryBag = null;

    public function __construct(
        private readonly Utils $utils,
        private readonly EntryCollection $entryCollection,
    ) {
    }

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

    public function setResponseContext(?ResponseContext $responseContext): self
    {
        $this->responseContext = $responseContext;

        return $this;
    }

    public function setCustomBag(?EntryBag $entryBag): self
    {
        $this->entryBag = $entryBag;

        return $this;
    }

    public function build(): EntryPoints
    {
        $entryPoints = new EntryPoints();
        $available = $this->entryCollection->getEntries();
        if ([] !== $available) {
            $available = array_combine(array_column($available, 'name'), $available);
        }
        $this->available = $available;

        if ($this->responseContext && $this->responseContext->has(EntryBag::class)) {
            $this->addFromBag($entryPoints, $this->responseContext->get(EntryBag::class));
        }

        if ($this->pageModel && !$this->layout) {
            $this->pageModel->loadDetails();
            $layout = LayoutModel::findByPk($this->pageModel->layoutId ?? $this->pageModel->layout);
            if ($layout) {
                $this->setLayout($layout);
            }
        }

        if ($this->layout) {
            foreach (StringUtil::deserialize($this->layout->{$this->layoutField}, true) as $entrypoint) {
                $this->addEntryPoint(
                    entryPoints: $entryPoints,
                    name: $entrypoint['entry'] ?? '',
                    active: (bool) ($entrypoint['active'] ?? true),
                    origin: 'tl_layout.' . $this->layout->id,
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
                        active: (bool) ($entrypoint['active'] ?? true),
                        origin: 'tl_page.' . $page->id,
                        extension: 'App',
                    );
                }
            }
        }

        if (null !== $this->entryBag) {
            $this->addFromBag($entryPoints, $this->entryBag);
        }

        return $entryPoints;
    }

    private function addFromBag(EntryPoints $entryPoints, EntryBag $bag): void
    {
        foreach ($bag->all() as $entry) {
            $this->addEntryPoint(
                entryPoints: $entryPoints,
                name: $entry->name,
                origin: $entry->origin,
                extension: $entry->extension,
            );
        }
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
            defer: $this->available[$name]['defer'] ?? null,
        ));
    }
}

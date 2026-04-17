<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntrypointsBuilder
{
    private ?PageModel $pageModel = null;
    private string $pageField = '';
    private ?LayoutModel $layout = null;
    private string $layoutField = '';

    public function __construct(
        private readonly Utils $utils,
        private readonly FrontendAsset $frontendAsset,
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

        foreach ($this->frontendAsset->getActiveEntrypoints() as $entrypoint) {
            $entrypoints->add(Entrypoint::fromString($entrypoint));
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
                $entrypoints->add(Entrypoint::fromArray($entrypoint));
            }
        }

        if (null !== $this->pageModel) {
            $pages = $this->utils->model()->findParentsRecursively($this->pageModel, 'pid');
            $pages[] = $this->pageModel;

            foreach ($pages as $page) {
                foreach (StringUtil::deserialize($page->{$this->pageField}, true) as $entrypoint) {
                    if (!isset($entrypoint['name'])) {
                        continue;
                    }
                    $entrypoints->add(Entrypoint::fromArray($entrypoint));
                }
            }
        }

        return $entrypoints;
    }
}
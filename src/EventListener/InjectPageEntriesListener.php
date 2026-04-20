<?php

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Event\LayoutEvent;
use Contao\LayoutModel;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class InjectPageEntriesListener
{
    public function __construct(
        private readonly TagRenderer              $tagRenderer,
        private readonly EntryPointBuilderFactory $entrypointBuilderFactory,
        private readonly FrontendAsset            $frontendAsset,
        private readonly GlobalContaoAsset $globalContaoAsset,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[AsEventListener]
    public function onLayoutEvent(LayoutEvent $event): void
    {
        if (!$this->isEnabled($event->getPage(), $event->getLayout())) {
            return;
        }

        $this->globalContaoAsset->cleanGlobalArrayFromConfiguration();

        $entryPoints = $this->entrypointBuilderFactory->create()
            ->setPage($event->getPage())
            ->setLayout($event->getLayout())
            ->setFrontendAsset($this->frontendAsset)
            ->build();

        $this->tagRenderer->reset();

        foreach ($entryPoints->allActive() as $entrypoint) {
            if ($entrypoint->requiresCss) {
                $GLOBALS['TL_HEAD'][] = $this->tagRenderer->renderWebpackLinkTags($entrypoint->name);
            }
            if ($entrypoint->head) {
                $GLOBALS['TL_HEAD'][] = $this->tagRenderer->renderWebpackScriptTags($entrypoint->name);
            } else {
                $GLOBALS['TL_BODY'][] = $this->tagRenderer->renderWebpackScriptTags($entrypoint->name);
            }
        }
    }

    private function isEnabled(PageModel $page, ?LayoutModel $layout): bool
    {
        if (!$layout) {
            $page->loadDetails();
            $layout = LayoutModel::findByPk($page->layout);
        }

        if (!$layout?->addEncore) {
            return false;
        }
    }
}
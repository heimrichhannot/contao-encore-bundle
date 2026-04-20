<?php

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Event\LayoutEvent;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class InjectPageEntriesListener
{
    public function __construct(
        private readonly TagRenderer              $tagRenderer,
        private readonly EntryPointBuilderFactory $entrypointBuilderFactory,
        private readonly FrontendAsset            $frontendAsset,
        private readonly GlobalContaoAsset $globalContaoAsset,
    ) {}

    #[AsEventListener]
    public function onLayoutEvent(LayoutEvent $event): void
    {
        if (!$event->getLayout()?->addEncore) {
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
}
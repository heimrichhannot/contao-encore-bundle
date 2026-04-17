<?php

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Event\LayoutEvent;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Entrypoints\EntrypointBuilderFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class InjectPageEntrypointsListener
{
    public function __construct(
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly TemplateAsset $templateAsset,
        private readonly TagRenderer $tagRenderer,
        private readonly PageEntrypoints $pageEntrypoints,
        private readonly EntrypointBuilderFactory $entrypointBuilderFactory,
    ) {}

    #[AsEventListener]
    public function onLayoutEvent(LayoutEvent $event): void
    {
        if (!$event->getLayout()?->addEncore) {
            return;
        }

        $entrypoints = $this->entrypointBuilderFactory->create()
            ->setPage($event->getPage())
            ->setLayout($event->getLayout())
            ->build();

        $this->tagRenderer->reset();

        foreach ($entrypoints->allActive() as $entrypoint) {
            $this->tagRenderer->renderWebpackScriptTags($entrypoint->name, extraAttributes: ['head' => $entrypoint->head]);
            if ($entrypoint->requiresCss) {
                $this->tagRenderer->renderWebpackLinkTags($entrypoint->name);
            }
        }

        $this->responseContextAccessor->getResponseContext()->get(HtmlHeadBag::class)->addLinkTag();




//        $this->pageEntrypoints->

//        $this->templateAsset->createInstance($event->getPage(), $event->getLayout(), 'encoreEntries');

//        $context = $this->responseContextAccessor->getResponseContext();
//        $context->get(HtmlHeadBag::class)->addLinkTag()
//        $context->


        return;
    }
}
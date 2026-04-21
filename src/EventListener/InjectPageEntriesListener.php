<?php

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Event\LayoutEvent;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class InjectPageEntriesListener
{
    public function __construct(
        private readonly TagRenderer $tagRenderer,
        private readonly EntryPointBuilderFactory $entrypointBuilderFactory,
        private readonly GlobalContaoAsset $globalContaoAsset,
        private readonly ConfigurationHelper $configurationHelper,
        private readonly RequestStack $requestStack,
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    #[AsEventListener]
    public function onLayoutEvent(LayoutEvent $event): void
    {
        if (!$this->configurationHelper->isEnabledOnPage($event->getPage(), $event->getLayout())) {
            return;
        }

        $loader = function () use ($event): string {
            $this->globalContaoAsset->cleanGlobalArrayFromConfiguration();

            $entryPoints = $this->entrypointBuilderFactory->create()
                ->setPage($event->getPage())
                ->setLayout($event->getLayout())
                ->setResponseContext($this->responseContextAccessor->getResponseContext())
                ->build();

            if ($request = $this->requestStack->getCurrentRequest()) {
                $request->attributes->add([
                    'encore_entries' => $entryPoints,
                ]);
            }

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

            return '';
        };

        $responseContext = $event->getTemplate()->get('response_context');
        if (!is_object($responseContext)) {
            $loader();

            return;
        }

        $responseContext = new class($responseContext, $loader) {
            public function __construct(
                private $responseContext,
                private readonly \Closure $loader,
            ) {
            }

            public function __get(string $key): mixed
            {
                if ('end_of_head' === $key) {
                    ($this->loader)();
                }

                return $this->responseContext->{$key};
            }

            public function __isset(string $key): bool
            {
                if ('end_of_head' === $key) {
                    return true;
                }

                return isset($this->responseContext->{$key});
            }

            public function __call(string $name, array $arguments): mixed
            {
                return $this->responseContext->{$name}(...$arguments);
            }
        };
        $event->getTemplate()->set('response_context', $responseContext);
    }
}

<?php

namespace HeimrichHannot\EncoreBundle\Test\EventListener;

use Contao\CoreBundle\Event\LayoutEvent;
use Contao\CoreBundle\Twig\LayoutTemplate;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoint;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointsBuilder;
use HeimrichHannot\EncoreBundle\EventListener\InjectPageEntriesListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

if (!class_exists(\Contao\CoreBundle\Twig\LayoutTemplate::class)) {
    class InjectPageEntriesTestLayoutTemplate
    {
        private array $data = [];

        public function __construct(string $name, ?callable $responseCallback = null)
        {
        }

        public function set(string $key, mixed $value): void
        {
            $this->data[$key] = $value;
        }

        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }

        public function has(string $key): bool
        {
            return \array_key_exists($key, $this->data);
        }
    }

    class_alias(InjectPageEntriesTestLayoutTemplate::class, \Contao\CoreBundle\Twig\LayoutTemplate::class);
}

if (!class_exists(\Contao\CoreBundle\Event\LayoutEvent::class)) {
    class InjectPageEntriesTestLayoutEvent
    {
        public function __construct(
            private readonly object $template,
            private readonly PageModel $page,
            private readonly LayoutModel $layout,
        ) {
        }

        public function getTemplate(): object
        {
            return $this->template;
        }

        public function getPage(): PageModel
        {
            return $this->page;
        }

        public function getLayout(): LayoutModel
        {
            return $this->layout;
        }
    }

    class_alias(InjectPageEntriesTestLayoutEvent::class, \Contao\CoreBundle\Event\LayoutEvent::class);
}

class InjectPageEntriesListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    private function createTestInstance(array $parameters = []): InjectPageEntriesListener
    {
        return new InjectPageEntriesListener(
            $parameters['tagRenderer'] ?? $this->createMock(TagRenderer::class),
            $parameters['entrypointBuilderFactory'] ?? $this->createMock(EntryPointBuilderFactory::class),
            $parameters['frontendAsset'] ?? $this->createMock(FrontendAsset::class),
            $parameters['globalContaoAsset'] ?? $this->createMock(GlobalContaoAsset::class),
            $parameters['configurationHelper'] ?? $this->createMock(ConfigurationHelper::class),
            $parameters['requestStack'] ?? $this->createMock(RequestStack::class),
        );
    }

    public function testOnLayoutEventLoadsEntrypointsViaLazyResponseContext(): void
    {
        $GLOBALS['TL_HEAD'] = [];
        $GLOBALS['TL_BODY'] = [];

        $page = $this->mockModelObject(PageModel::class, ['type' => 'regular']);
        $layout = $this->mockClassWithProperties(LayoutModel::class, ['customOption' => true]);

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->expects($this->once())
            ->method('isEnabledOnPage')
            ->with($page, $layout)
            ->willReturn(true);

        $entryPoints = new EntryPoints();
        $entryPoints->add(new EntryPoint('app', head: true, requiresCss: true));
        $entryPoints->add(new EntryPoint('deferred', head: false, requiresCss: false));

        $builder = $this->createMock(EntryPointsBuilder::class);
        $builder->expects($this->once())->method('setPage')->with($page)->willReturnSelf();
        $builder->expects($this->once())->method('setLayout')->with($layout)->willReturnSelf();
        $builder->expects($this->once())->method('setFrontendAsset')->with($this->isInstanceOf(FrontendAsset::class))->willReturnSelf();
        $builder->expects($this->once())->method('build')->willReturn($entryPoints);

        $entryPointBuilderFactory = $this->createMock(EntryPointBuilderFactory::class);
        $entryPointBuilderFactory->expects($this->once())->method('create')->willReturn($builder);

        $globalContaoAsset = $this->createMock(GlobalContaoAsset::class);
        $globalContaoAsset->expects($this->once())->method('cleanGlobalArrayFromConfiguration');

        $tagRenderer = $this->createMock(TagRenderer::class);
        $tagRenderer->expects($this->once())->method('reset');
        $tagRenderer->expects($this->once())->method('renderWebpackLinkTags')->with('app')->willReturn('<link-app>');
        $tagRenderer->expects($this->exactly(2))
            ->method('renderWebpackScriptTags')
            ->willReturnCallback(static fn (string $entryName): string => match ($entryName) {
                'app' => '<script-app>',
                'deferred' => '<script-deferred>',
                default => throw new \InvalidArgumentException(sprintf('Unexpected entry "%s".', $entryName)),
            });

        $request = new Request();
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

        $template = new LayoutTemplate('layout', static fn () => new Response());
        $responseContext = new class() {
            public string $end_of_head = 'existing-head';
            public string $other = 'other-value';

            public function ping(string $value): string
            {
                return 'pong-'.$value;
            }
        };
        $template->set('response_context', $responseContext);

        $listener = $this->createTestInstance([
            'tagRenderer' => $tagRenderer,
            'entrypointBuilderFactory' => $entryPointBuilderFactory,
            'globalContaoAsset' => $globalContaoAsset,
            'configurationHelper' => $configurationHelper,
            'requestStack' => $requestStack,
        ]);

        $listener->onLayoutEvent(new LayoutEvent($template, $page, $layout));

        $this->assertSame('Lorem Ipsum', $template->get('customAttribute'));

        $wrappedResponseContext = $template->get('response_context');
        $this->assertSame('other-value', $wrappedResponseContext->other);
        $this->assertTrue(isset($wrappedResponseContext->end_of_head));
        $this->assertSame('pong-demo', $wrappedResponseContext->ping('demo'));
        $this->assertSame([], $GLOBALS['TL_HEAD']);
        $this->assertSame([], $GLOBALS['TL_BODY']);

        $this->assertSame('existing-head', $wrappedResponseContext->end_of_head);
        $this->assertSame($entryPoints, $request->attributes->get('encore_entries'));
        $this->assertSame(['<link-app>', '<script-app>'], $GLOBALS['TL_HEAD']);
        $this->assertSame(['<script-deferred>'], $GLOBALS['TL_BODY']);
    }

    public function testOnLayoutEventReturnsEarlyForNonRegularPages(): void
    {
        $page = $this->mockModelObject(PageModel::class, ['type' => 'error_404']);
        $layout = $this->mockClassWithProperties(LayoutModel::class, ['customOption' => false]);

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->expects($this->never())->method('isEnabledOnPage');

        $template = new LayoutTemplate('layout', static fn () => new Response());

        $listener = $this->createTestInstance([
            'configurationHelper' => $configurationHelper,
        ]);

        $listener->onLayoutEvent(new LayoutEvent($template, $page, $layout));

        $this->assertFalse($template->has('customAttribute'));
    }
}

<?php

namespace HeimrichHannot\EncoreBundle\Test\Integration;

use Contao\CoreBundle\Event\LayoutEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\LayoutTemplate;
use Contao\LayoutModel;
use Contao\Model\Registry;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use Contao\ThemeModel;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\EventListener\Contao\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\EncoreBundle\EventListener\InjectPageEntriesListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

// Compatibility fixtures for Contao versions before LayoutEvent existed.
require_once __DIR__ . '/../EventListener/InjectPageEntriesListenerTest.php';

class ThemeEntryRenderingTest extends ContaoTestCase
{
    use ModelMockTrait;

    /** @dataProvider renderingProvider */
    public function testThemeAssetsRenderThroughRealBuilderAndRenderer(bool $modern, bool $enabled): void
    {
        $GLOBALS['TL_HEAD'] = [];
        $GLOBALS['TL_BODY'] = [];
        $theme = $this->mockModelObject(ThemeModel::class, ['id' => 70, 'addEncore' => '1', 'encoreEntries' => serialize([
            ['entry' => 'app'], ['entry' => 'deferred'], ['entry' => 'disabled'],
        ])]);
        $layout = $this->getMockBuilder(LayoutModel::class)->disableOriginalConstructor()->onlyMethods(['getRelated', 'onRegister', 'onUnregister'])->getMock();
        (new \ReflectionProperty(LayoutModel::class, 'arrData'))->setValue($layout, ['id' => 80, 'pid' => 70, 'type' => $modern ? 'modern' : 'default', 'addEncore' => $enabled ? 'i' : '0', 'encoreEntries' => serialize([
            ['entry' => 'disabled', 'active' => ''],
        ])]);
        $layout->method('getRelated')->with('pid')->willReturn($theme);
        $page = $this->mockModelObject(PageModel::class, ['id' => 90, 'layoutId' => 80, 'type' => 'regular']);
        $previousModel = $GLOBALS['TL_MODELS']['tl_layout'] ?? null;
        $GLOBALS['TL_MODELS']['tl_layout'] = LayoutModel::class;
        Registry::getInstance()->register($layout);
        $requestStack = new RequestStack();
        $request = new Request([], [], ['pageModel' => $page]);
        $requestStack->push($request);
        $scope = $this->createMock(ScopeMatcher::class);
        $scope->method('isFrontendRequest')->willReturn(true);
        $layoutAdapter = $this->mockAdapter(['findByPk']);
        $layoutAdapter->method('findByPk')->with(80)->willReturn($layout);
        $framework = $this->mockContaoFramework([LayoutModel::class => $layoutAdapter]);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);
        $configuration = new ConfigurationHelper($requestStack, new ParameterBag(), $scope, $framework, $dispatcher);
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findParentsRecursively')->willReturn([]);
        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn($page);
        $utils = $this->createMock(Utils::class);
        $utils->method('model')->willReturn($modelUtil);
        $utils->method('request')->willReturn($requestUtil);
        $collection = $this->createMock(EntryCollection::class);
        $collection->method('getEntries')->willReturn([
            EncoreEntry::create('app', '')->setRequiresCss(true)->setIsHeadScript(true),
            EncoreEntry::create('deferred', ''),
            EncoreEntry::create('disabled', '')->setRequiresCss(true),
        ]);
        $factory = new EntryPointBuilderFactory($utils, $collection);
        $manifest = tempnam(sys_get_temp_dir(), 'encore-theme-');
        file_put_contents($manifest, json_encode(['entrypoints' => [
            'app' => ['css' => ['/build/app.css'], 'js' => ['/build/app.js']],
            'deferred' => ['js' => ['/build/deferred.js']],
            'disabled' => ['js' => ['/build/disabled.js']],
        ]], JSON_THROW_ON_ERROR));

        try {
            $lookup = new EntrypointLookup($manifest);
            $renderer = new TagRenderer(
                new EntrypointLookupCollection(new ServiceLocator(['_default' => static fn () => $lookup])),
                new Packages(new Package(new EmptyVersionStrategy())),
            );
            $globalAssets = $this->createMock(GlobalContaoAsset::class);
            $accessor = new ResponseContextAccessor($requestStack);
            if ($modern) {
                $template = new LayoutTemplate('layout', static fn () => new Response());
                $template->set('response_context', (object) ['end_of_head' => '']);
                $listener = new InjectPageEntriesListener($renderer, $factory, $globalAssets, $configuration, $requestStack, $accessor);
                $listener->onLayoutEvent(new LayoutEvent($template, $page, $layout));
                $template->get('response_context')->end_of_head;
                $head = implode('', $GLOBALS['TL_HEAD']);
                $body = implode('', $GLOBALS['TL_BODY']);
            } else {
                $listener = new ReplaceDynamicScriptTagsListener($utils, $configuration, $globalAssets, $factory, $renderer, $requestStack, $accessor);
                $nonce = '_' . ContaoFramework::getNonce();
                $head = $listener("[[TL_CSS$nonce]][[TL_HEAD$nonce]]");
                $body = $listener("[[TL_BODY$nonce]]");
            }
            $this->assertSame($enabled ? 1 : 0, substr_count($head, '<link rel="stylesheet" href="/build/app.css">'));
            $this->assertSame($enabled ? 1 : 0, substr_count($head, '<script src="/build/app.js"></script>'));
            $this->assertSame($enabled ? 1 : 0, substr_count($body, '<script src="/build/deferred.js"></script>'));
            $this->assertStringNotContainsString('/build/disabled.js', $head . $body);
        } finally {
            Registry::getInstance()->unregister($layout);
            if (null === $previousModel) {
                unset($GLOBALS['TL_MODELS']['tl_layout']);
            } else {
                $GLOBALS['TL_MODELS']['tl_layout'] = $previousModel;
            }
            unlink($manifest);
            unset($GLOBALS['TL_HEAD'], $GLOBALS['TL_BODY']);
        }
    }

    public function renderingProvider(): array
    {
        return [[true, true], [true, false], [false, true], [false, false]];
    }
}

<?php

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoint;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointsBuilder;
use HeimrichHannot\EncoreBundle\EventListener\Contao\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\UtilsBundle\Util\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class FragmentAssetConsumptionTest extends TestCase
{
    public function testFragmentsDoNotConsumeAssetsWithoutTheirPlaceholders(): void
    {
        $manifest = tempnam(sys_get_temp_dir(), 'encore-entries-');
        file_put_contents($manifest, json_encode(['entrypoints' => [
            'app' => ['css' => ['/build/app.css'], 'js' => ['/build/app.js']],
            'deferred' => ['js' => ['/build/deferred.js']],
        ]], JSON_THROW_ON_ERROR));

        $nonce = '_' . ContaoFramework::getNonce();
        $placeholders = ["[[TL_CSS$nonce]]", "[[TL_HEAD$nonce]]", "[[TL_BODY$nonce]]"];
        $assets = ['/build/app.css', '/build/app.js', '/build/deferred.js'];

        try {
            foreach (['<div>Credits</div>', '[[TL_CSS_wrong_nonce]]', ...$placeholders] as $fragment) {
                $lookup = new EntrypointLookup($manifest);
                $renderer = new TagRenderer(
                    new EntrypointLookupCollection(new ServiceLocator(['_default' => static fn () => $lookup])),
                    new Packages(new Package(new EmptyVersionStrategy())),
                );
                $page = $this->createStub(PageModel::class);
                $requestUtil = $this->createStub(RequestUtil::class);
                $requestUtil->method('getCurrentPageModel')->willReturn($page);
                $utils = $this->createStub(Utils::class);
                $utils->method('request')->willReturn($requestUtil);
                $configuration = $this->createStub(ConfigurationHelper::class);
                $configuration->method('isEnabledOnPage')->willReturn(true);

                $entries = new EntryPoints();
                $entries->add(new EntryPoint('app', head: true, requiresCss: true));
                $entries->add(new EntryPoint('deferred', head: false, requiresCss: false));
                $builder = $this->createStub(EntryPointsBuilder::class);
                $builder->method('setResponseContext')->willReturnSelf();
                $builder->method('setPage')->willReturnSelf();
                $builder->method('build')->willReturn($entries);
                $factory = $this->createStub(EntryPointBuilderFactory::class);
                $factory->method('create')->willReturn($builder);
                $requestStack = new RequestStack();
                $requestStack->push(new Request());
                $listener = new ReplaceDynamicScriptTagsListener(
                    $utils,
                    $configuration,
                    $this->createStub(GlobalContaoAsset::class),
                    $factory,
                    $renderer,
                    $requestStack,
                    new ResponseContextAccessor($requestStack),
                );

                $fragmentOutput = $listener($fragment);
                $pageOutput = $listener(implode(' ', $placeholders));

                foreach ($assets as $index => $asset) {
                    $inFragment = $fragment === $placeholders[$index];
                    $this->assertSame($inFragment ? 1 : 0, substr_count($fragmentOutput, $asset), $fragment);
                    $this->assertSame($inFragment ? 0 : 1, substr_count($pageOutput, $asset), $fragment);
                }
                if (!in_array($fragment, $placeholders, true)) {
                    $this->assertSame($fragment, $fragmentOutput);
                }
            }
        } finally {
            unlink($manifest);
        }
    }
}

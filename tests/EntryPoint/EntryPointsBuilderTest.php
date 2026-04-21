<?php

namespace HeimrichHannot\EncoreBundle\Test\EntryPoint;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoint;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointsBuilder;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EntryPointsBuilderTest extends ContaoTestCase
{
    use ModelMockTrait;

    private function createFrontendAsset(): FrontendAsset
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $responseContextAccessor = new ResponseContextAccessor($requestStack);
        $responseContextAccessor->setResponseContext(new ResponseContext());

        return new FrontendAsset($responseContextAccessor);
    }

    public function testFactoryCreatesFreshBuilderInstances(): void
    {
        $utils = $this->createMock(Utils::class);
        $entryCollection = $this->createMock(EntryCollection::class);

        $factory = new EntryPointBuilderFactory($utils, $entryCollection);

        $firstBuilder = $factory->create();
        $secondBuilder = $factory->create();

        $this->assertInstanceOf(EntryPointsBuilder::class, $firstBuilder);
        $this->assertInstanceOf(EntryPointsBuilder::class, $secondBuilder);
        $this->assertNotSame($firstBuilder, $secondBuilder);
    }

    public function testEntryPointsTracksAllAndActiveEntries(): void
    {
        $entryPoint = new EntryPoint(
            name: 'app',
            active: true,
            head: true,
            requiresCss: true,
            origin: 'frontend',
            extension: 'App',
        );

        $this->assertSame('app', $entryPoint->name);
        $this->assertTrue($entryPoint->active);
        $this->assertTrue($entryPoint->head);
        $this->assertTrue($entryPoint->requiresCss);
        $this->assertSame('frontend', $entryPoint->origin);
        $this->assertSame('App', $entryPoint->extension);

        $entryPoints = new EntryPoints();
        $entryPoints->add($entryPoint);
        $entryPoints->add(new EntryPoint('deferred', head: false, requiresCss: false));
        $entryPoints->add(new EntryPoint('app', active: false, origin: 'tl_page.1'));

        $all = $entryPoints->all();
        $active = $entryPoints->allActive();

        $this->assertCount(2, $all);
        $this->assertCount(1, $active);
        $this->assertFalse($all['app']->active);
        $this->assertSame('tl_page.1', $all['app']->origin);
        $this->assertArrayNotHasKey('app', $active);
        $this->assertSame('deferred', $active['deferred']->name);
    }

    public function testBuildCombinesFrontendLayoutAndPageEntries(): void
    {
        $entryCollection = $this->createMock(EntryCollection::class);
        $entryCollection->expects($this->once())
            ->method('getEntries')
            ->willReturn([
                ['name' => 'frontend-entry', 'requires_css' => false],
                ['name' => 'layout-entry', 'head' => true, 'requires_css' => true],
                ['name' => 'shared-entry', 'head' => false, 'requires_css' => true],
                ['name' => 'parent-entry'],
                ['name' => 'page-entry', 'requires_css' => false],
            ]);

        $parentPage = $this->mockModelObject(PageModel::class, [
            'id' => 2,
            'customEntries' => serialize([
                ['entry' => 'shared-entry', 'active' => '1'],
                ['entry' => 'parent-entry', 'active' => '1'],
                ['entry' => 'missing-entry', 'active' => '1'],
            ]),
        ]);

        $page = $this->mockModelObject(PageModel::class, [
            'id' => 3,
            'customEntries' => serialize([
                ['entry' => 'shared-entry', 'active' => ''],
                ['entry' => 'page-entry', 'active' => '1'],
                ['entry' => '', 'active' => '1'],
            ]),
        ]);

        $layout = $this->mockClassWithProperties(LayoutModel::class, [
            'id' => 5,
            'layoutEntries' => serialize([
                ['entry' => 'layout-entry', 'active' => '1'],
                ['entry' => 'missing-layout-entry', 'active' => '1'],
            ]),
        ]);

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->expects($this->once())
            ->method('findParentsRecursively')
            ->with($page, 'pid')
            ->willReturn([$parentPage]);

        $utils = $this->createMock(Utils::class);
        $utils->expects($this->once())
            ->method('model')
            ->willReturn($modelUtil);

        $frontendAsset = $this->createFrontendAsset();
        $frontendAsset->addActiveEntrypoint('frontend-entry');
        $frontendAsset->addActiveEntrypoint('missing-frontend-entry');

        $builder = new EntryPointsBuilder($utils, $entryCollection);
        $result = $builder
            ->setFrontendAsset($frontendAsset)
            ->setLayout($layout, 'layoutEntries')
            ->setPage($page, 'customEntries')
            ->build();

        $this->assertInstanceOf(EntryPoints::class, $result);

        $all = $result->all();
        $active = $result->allActive();

        $this->assertSame(
            ['frontend-entry', 'layout-entry', 'shared-entry', 'parent-entry', 'page-entry'],
            array_keys($all)
        );
        $this->assertSame(
            ['frontend-entry', 'layout-entry', 'parent-entry', 'page-entry'],
            array_keys($active)
        );

        $this->assertSame(FrontendAsset::class, $all['frontend-entry']->origin);
        $this->assertFalse($all['frontend-entry']->requiresCss);
        $this->assertTrue($all['layout-entry']->head);
        $this->assertTrue($all['layout-entry']->requiresCss);
        $this->assertSame('tl_layout.5', $all['layout-entry']->origin);
        $this->assertSame('App', $all['layout-entry']->extension);
        $this->assertFalse($all['shared-entry']->active);
        $this->assertSame('tl_page.3', $all['shared-entry']->origin);
        $this->assertArrayNotHasKey('shared-entry', $active);
        $this->assertTrue($all['parent-entry']->requiresCss);
        $this->assertSame('tl_page.2', $all['parent-entry']->origin);
        $this->assertFalse($all['page-entry']->head);
        $this->assertFalse($all['page-entry']->requiresCss);
        $this->assertSame('tl_page.3', $all['page-entry']->origin);
    }
}

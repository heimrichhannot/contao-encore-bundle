<?php

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\EventListener\Contao\ParseTemplateListener;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\TestUtilitiesBundle\Mock\TemplateMockTrait;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\MockObject\MockBuilder;

class ParseTemplateListenerTest extends ContaoTestCase
{
    use TemplateMockTrait;
    use ModelMockTrait;

    public function getTestInstance(array $parameter = [], ?MockBuilder $mockBuilder = null)
    {
        $utils = $parameter['utils'] ?? $this->createMock(Utils::class);
        $bundleConfig = $parameter['bundleConfig'] ?? [];

        return new ParseTemplateListener($utils, $bundleConfig);
    }

    public function testInvoke(): void
    {
        $instance = $this->getTestInstance();

        $template = $this->mockTemplateObject(FrontendTemplate::class);
        $instance->__invoke($template);
        $this->assertEmpty($template->encoreStylesheets);

        $template->setName('fe_page');
        $instance->__invoke($template);
        $this->assertEmpty($template->encoreStylesheets);

        $instance = $this->getTestInstance(['bundleConfig' => ['use_contao_template_variables' => false]]);
        $template = $this->mockTemplateObject(FrontendTemplate::class, 'fe_page_test');
        $this->assertEmpty($template->encoreStylesheets);

        $layout = $this->mockModelObject(LayoutModel::class, ['addEncore' => true]);
        $template->layout = $layout;
        $instance->__invoke($template);
        $this->assertSame('[[HUH_ENCORE_CSS]]', $template->encoreStylesheets);

        $instance = $this->getTestInstance(['bundleConfig' => ['use_contao_template_variables' => true]]);
        $template = $this->mockTemplateObject(FrontendTemplate::class, 'fe_page_test');
        $instance->__invoke($template);
        $this->assertEmpty($template->encoreStylesheets);


        $utils = $this->createMock(Utils::class);
        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn($this->mockModelObject(PageModel::class, ['layout' => 5]));
        $utils->method('request')->willReturn($requestUtil);
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturn($this->mockModelObject(LayoutModel::class, ['id' => 5, 'addEncore' => '1']));
        $utils->method('model')->willReturn($modelUtil);

        $instance = $this->getTestInstance([
            'utils' => $utils,
            'bundleConfig' => ['use_contao_template_variables' => false
            ]]);
        $template = $this->mockTemplateObject(FrontendTemplate::class, 'fe_page_test');
        $instance->__invoke($template);
        $this->assertSame('[[HUH_ENCORE_CSS]]', $template->encoreStylesheets);


    }
}
<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\Asset;


use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Error\LoaderError;

class TemplateAssetTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [], $instanceMock = null)
    {
        if (!isset($parameters['bundleConfig']))
        {
            $parameters['bundleConfig'] = [
                'templates' => [
                    'imports' => [
                        ['name' => 'default_css', 'template' => '@HeimrichHannotContaoEncore/encore_css_imports.html.twig'],
                        ['name' => 'default_js', 'template' => '@HeimrichHannotContaoEncore/encore_js_imports.html.twig'],
                        ['name' => 'default_head_js', 'template' => '@HeimrichHannotContaoEncore/encore_head_js_imports.html.twig'],
                    ]
                ]
            ];
        }
        if (!isset($parameters['webDir']))
        {
            $parameters['webDir'] = $this->getTempDir().'/web';
        }
        if (!isset($parameters['twig'])) {
            $twig = $this->createMock(Environment::class);
            $twig->method('render')->willReturnCallback(function ($template, $data) {
                if (empty($template)) {
                    throw new LoaderError('No template provided');
                }
                return serialize(['template' => $template, 'data' => $data]);
            });
            $parameters['twig'] = $twig;
        }
        if (!isset($parameters['pageEntrypoints'])) {
            $pageEntrypoints = $this->createMock(PageEntrypoints::class);
            $pageEntrypoints->method('generatePageEntrypoints')->willReturn(true);
            $pageEntrypoints->method('createInstance')->willReturnSelf();
            $parameters['pageEntrypoints'] = $pageEntrypoints;
        }
        $instance = new TemplateAsset($parameters['bundleConfig'], $parameters['webDir'], $parameters['twig'], $parameters['pageEntrypoints']);
        return $instance;
    }

    public function testCreateInstance()
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);

        $instance = $this->createTestInstance();
        $newInstance = $instance->createInstance($pageModel, $layoutModel);

        $this->assertInstanceOf(TemplateAsset::class, $newInstance);
        $this->assertNotSame($instance, $newInstance);

        $pageEntrypoints = $this->createMock(PageEntrypoints::class);
        $pageEntrypoints->method('generatePageEntrypoints')->willReturn(false);
        $pageEntrypoints->method('createInstance')->willReturnSelf();

        $instance = $this->createTestInstance(['pageEntrypoints' => $pageEntrypoints])->createInstance($pageModel, $layoutModel);
        $this->expectException(\Exception::class);
        $instance->scriptTags();

    }

    public function testGenerateTags()
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $instance = $this->createTestInstance()->createInstance($pageModel, $layoutModel);
        $result = unserialize($instance->headScriptTags());
        $this->assertStringStartsWith('@', $result['template']);

        $instance = $this->createTestInstance(['bundleConfig' => []])->createInstance($pageModel, $layoutModel);
        $this->expectException(LoaderError::class);
        $instance->linkTags();
    }


    public function testInlineCssLinkTag()
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $instance = $this->createTestInstance()->createInstance($pageModel, $layoutModel);
        $this->assertFalse($instance->inlineCssLinkTag());

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<link rel="stylesheet" href="/styles.css">');
        $instance = $this->createTestInstance(['twig' => $twig])->createInstance($pageModel, $layoutModel);
        $webDir = $this->getTempDir().'/web';
        $filesystem = new Filesystem();
        $filesystem->dumpFile($webDir.'/styles.css', '.style{}');
        $this->assertSame('.style{}', $instance->inlineCssLinkTag());
    }

    public function testGetItemTemplateByName()
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $instance = $this->createTestInstance(['bundleConfig' => ['templates' => ['imports' => [['name' => 'default_css', 'template' => 'encore_css_imports.html.twig']]]]])->createInstance($pageModel, $layoutModel);
        $result = unserialize($instance->linkTags());
        $this->assertSame('encore_css_imports.html.twig', $result['template']);

        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, ['encoreStylesheetsImportsTemplate' => 'another_css']);
        $instance = $this->createTestInstance()->createInstance($pageModel, $layoutModel);
        $this->expectException(LoaderError::class);
        $instance->linkTags();
    }
}
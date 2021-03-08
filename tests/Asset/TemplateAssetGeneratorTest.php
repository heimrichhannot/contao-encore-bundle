<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollection;
use HeimrichHannot\EncoreBundle\Asset\TemplateAssetGenerator;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;

class TemplateAssetGeneratorTest extends ContaoTestCase
{
    public function testInstance()
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function ($template, $data) {
            if (!\is_string($template)) {
                throw new LoaderError('No template found');
            }
            if ('@DefaultHeadJs' == $template && isset($data['jsHeadEntries']) && !empty($data['jsHeadEntries'])) {
                return $template.implode(',', $data['jsHeadEntries']);
            }
            if ('@MissingEntrypoint' == $template) {
                throw new RuntimeError('Missing entrypoint', -1, null, new EntrypointNotFoundException());
            }
            if ('@RuntimeError' == $template) {
                throw new RuntimeError('');
            }

            return $template;
        });
        $bundleConfig = [];
        $instance = new TemplateAssetGenerator($twig, $bundleConfig);

        $collection = new EntrypointCollection();
        $collection->addJsHeadEntry('contao-head-bundle');

        $exception = false;
        try {
            $instance->linkTags($collection);
        } catch (LoaderError $e) {
            $exception = true;
        }
        $this->assertTrue($exception);

        $bundleConfig = ['templates' => ['imports' => [
            ['name' => 'never-used-template', 'template' => '@NeverUsedTemplate'],
        ]]];
        $instance = new TemplateAssetGenerator($twig, $bundleConfig);
        $exception = false;
        try {
            $instance->linkTags($collection);
        } catch (LoaderError $e) {
            $exception = true;
        }
        $this->assertTrue($exception);

        $bundleConfig = ['templates' => ['imports' => [
            ['name' => 'default_css', 'template' => '@DefaultCss'],
            ['name' => 'custom_css', 'template' => '@CustomCss'],
            ['name' => 'default_head_js', 'template' => '@DefaultHeadJs'],
            ['name' => 'default_js', 'template' => '@DefaultJs'],
            ['name' => 'missing_entrypoint', 'template' => '@MissingEntrypoint'],
            ['name' => 'runtime_error', 'template' => '@RuntimeError'],
        ]]];
        $instance = new TemplateAssetGenerator($twig, $bundleConfig);

        $this->assertSame('@DefaultCss', $instance->linkTags($collection));
        $this->assertSame('@CustomCss', $instance->linkTags($collection, 'custom_css'));

        $this->assertSame('@DefaultJs', $instance->scriptTags($collection));

        $this->assertSame('@DefaultHeadJscontao-head-bundle', $instance->headScriptTags($collection));

        $runtimeError = false;
        try {
            $instance->scriptTags($collection, 'runtime_error');
        } catch (RuntimeError $e) {
            $runtimeError = true;
        }
        $this->assertTrue($runtimeError);

        $this->expectException(EntrypointNotFoundException::class);
        $instance->headScriptTags($collection, 'missing_entrypoint');
    }
}

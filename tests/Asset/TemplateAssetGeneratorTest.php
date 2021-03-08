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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;

class TemplateAssetGeneratorTest extends ContaoTestCase
{
    public function createInstance(array $parameters)
    {
        if (!isset($parameters['bundleConfig'])) {
            $parameters['bundleConfig'] = [];
        }
        if (!isset($parameters['webDir'])) {
            $parameters['webDir'] = $this->getTempDir().'/web';
        }

        return new TemplateAssetGenerator($parameters['twig'], $parameters['bundleConfig'], $parameters['webDir']);
    }

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
            if ('@InlineCss' == $template) {
                return '<link rel="stylesheet" href="/styles.css">';
            }
            if ('@EmptyInlineCss' == $template) {
                return '';
            }

            return $template;
        });
        $instance = $this->createInstance([
            'twig' => $twig,
        ]);

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

        $instance = $this->createInstance([
            'twig' => $twig,
            'bundleConfig' => $bundleConfig,
        ]);
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
            ['name' => 'inline_css', 'template' => '@InlineCss'],
            ['name' => 'empty_inline_css', 'template' => '@EmptyInlineCss'],
        ]]];
        $instance = $this->createInstance([
            'twig' => $twig,
            'bundleConfig' => $bundleConfig,
        ]);

        $this->assertSame('@DefaultCss', $instance->linkTags($collection));
        $this->assertSame('@CustomCss', $instance->linkTags($collection, 'custom_css'));

        $this->assertSame('@DefaultJs', $instance->scriptTags($collection));

        $this->assertSame('@DefaultHeadJscontao-head-bundle', $instance->headScriptTags($collection));

        $webDir = $this->getTempDir().'/web';
        $filesystem = new Filesystem();
        $filesystem->dumpFile($webDir.'/styles.css', '.style{}');
        $this->assertSame('.style{}', $instance->inlineCssLinkTag($collection, 'inline_css'));

        $this->assertEmpty($instance->inlineCssLinkTag($collection, 'empty_inline_css'));

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

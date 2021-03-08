<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TemplateAssetGenerator
{
    /**
     * @var Environment
     */
    protected $twig;
    /**
     * @var array
     */
    protected $bundleConfig;

    /**
     * TemplateAssetGenerator constructor.
     */
    public function __construct(Environment $twig, array $bundleConfig)
    {
        $this->twig = $twig;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * Return the css link tags that should be included in the header region.
     *
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function linkTags(EntrypointCollection $collection, string $template = ''): string
    {
        return $this->generateTags($collection, $template, 'default_css');
    }

    /**
     * Return the javascript that should be included in the header region.
     *
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function headScriptTags(EntrypointCollection $collection, string $template = ''): string
    {
        return $this->generateTags($collection, $template, 'default_head_js');
    }

    /**
     * Return the javascript tags that should be included in the footer region.
     *
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function scriptTags(EntrypointCollection $collection, string $template = ''): string
    {
        return $this->generateTags($collection, $template, 'default_js');
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     */
    protected function generateTags(EntrypointCollection $collection, string $template, string $defaultTemplate): string
    {
        $template = $this->getItemTemplateByName($template ?: $defaultTemplate);
        $templateData = $collection->getTemplateData();

        try {
            return $this->twig->render($template, $templateData);
        } catch (RuntimeError $e) {
            if (($previous = $e->getPrevious())) {
                if ($previous instanceof EntrypointNotFoundException) {
                    throw new EntrypointNotFoundException($previous->getMessage().' Maybe you forgot to run prepare or encore command?', $previous->getCode());
                }
            }
            throw $e;
        }
    }

    protected function getItemTemplateByName(string $name)
    {
        if (!isset($this->bundleConfig['templates']['imports'])) {
            return null;
        }

        $templates = $this->bundleConfig['templates']['imports'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }
}

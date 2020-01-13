<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HookListener
 * @package HeimrichHannot\EncoreBundle\EventListener
 *
 * @todo Remove in version 2.0
 */
class HookListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface    $container
     * @codeCoverageIgnore
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * generatePage Hook
     *
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @deprecated Will be removed in 2.0. Use GeneratePageListener::onGeneratePage instead
     * @codeCoverageIgnore
     */
    public function onGeneratePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $this->container->get(GeneratePageListener::class)->onGeneratePage($page, $layout, $pageRegular);
    }

    /**
     * A alias for the addEncore method. Just for backward compatibility. Will be removed in next major version.
     *
     * @deprecated Use GeneratePageListener::addEncore method instead. Will be removed in next major release.
     * @codeCoverageIgnore
     *
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function doAddEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false)
    {
        $this->container->get(GeneratePageListener::class)->addEncore($page, $layout, $pageRegular, $encoreField, $includeInline);
    }

    /**
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     *
     * @deprecated Use GeneratePageListener::addEncore method instead. Will be removed in next major release.
     * @codeCoverageIgnore
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        $this->container->get(GeneratePageListener::class)->addEncore($page, $layout, $pageRegular, $encoreField, $includeInline);
    }

    /**
     * @deprecated Use GeneratePageListener::cleanGlobalArrays method instead. Will be removed in next major release.
     * @codeCoverageIgnore
     */
    public function cleanGlobalArrays()
    {
        /* @var PageModel $objPage */
        global $objPage;

        /** @var LayoutModel $layout */
        $layout = $this->framework->getAdapter(LayoutModel::class);

        if (null === ($layout = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_layout', $objPage->layout)) || !$layout->addEncore) {
            return;
        }
    }
}
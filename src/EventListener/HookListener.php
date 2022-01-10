<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HookListener.
 *
 * @deprecated Will be removed in next major verison.
 * @codeCoverageIgnore 
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
     * @codeCoverageIgnore
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * generatePage Hook.
     *
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function doAddEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false)
    {
        $this->container->get(GeneratePageListener::class)->addEncore($page, $layout, $pageRegular, $encoreField, $includeInline);
    }

    /**
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
     * @deprecated Will be removed in next major release. Use EntryHelper::cleanGlobalArrays() method instead.
     * @codeCoverageIgnore
     */
    public function cleanGlobalArrays()
    {
        if (!$this->container->get('huh.utils.container')->isFrontend()) {
            return;
        }

        EntryHelper::cleanGlobalArrays($this->container->getParameter('huh_encore'));
    }
}

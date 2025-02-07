<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;

class GetPageLayoutListener
{
    protected array                               $encoreBuildNames;

    /**
     * GetPageLayoutListener constructor.
     */
    public function __construct(protected EntrypointLookupCollectionInterface $entrypointLookupCollection)
    {
    }

    #[AsHook('getPageLayout')]
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ('error_404' === $pageModel->type) {
            $this->entrypointLookupCollection->getEntrypointLookup()->reset();
        }
    }
}

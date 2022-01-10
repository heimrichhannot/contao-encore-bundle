<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;

class GetPageLayoutListener
{
    /**
     * @var EntrypointLookupCollection
     */
    protected $entrypointLookupCollection;
    /**
     * @var array
     */
    protected $encoreBuildNames;

    /**
     * GetPageLayoutListener constructor.
     */
    public function __construct(EntrypointLookupCollectionInterface $entrypointLookupCollection)
    {
        $this->entrypointLookupCollection = $entrypointLookupCollection;
    }

    /**
     * @Hook("getPageLayout")
     */
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ('error_404' === $pageModel->type) {
            $this->entrypointLookupCollection->getEntrypointLookup()->reset();
        }
    }
}

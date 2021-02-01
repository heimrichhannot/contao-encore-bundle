<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\EventListener\Contao\GetPageLayoutListener;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class GetPageLayoutListenerTest extends ContaoTestCase
{
    public function testInvoke()
    {
        $collection = $this->createMock(EntrypointLookupCollectionInterface::class);
        $collection->expects($this->once())->method('getEntrypointLookup')->willReturn($this->createMock(EntrypointLookupInterface::class));
        $instance = new GetPageLayoutListener($collection);
        $page = $this->mockClassWithProperties(PageModel::class, ['type' => 'regular']);
        $layout = $this->mockClassWithProperties(LayoutModel::class, []);
        $regular = $this->mockClassWithProperties(PageRegular::class, []);
        $instance->__invoke($page, $layout, $regular);

        $page = $this->mockClassWithProperties(PageModel::class, ['type' => 'error_404']);
        $instance->__invoke($page, $layout, $regular);
    }
}

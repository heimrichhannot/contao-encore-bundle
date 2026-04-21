<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Asset;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Request\ResponseContext\Entry;
use HeimrichHannot\EncoreBundle\Request\ResponseContext\EntryBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendAssetTest extends ContaoTestCase
{
    public function testEntrypoints(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $responseContextAccessor = new ResponseContextAccessor($requestStack);
        $responseContext = new ResponseContext();
        $responseContextAccessor->setResponseContext($responseContext);

        $frontendAsset = new FrontendAsset($responseContextAccessor);
        $frontendAsset->addActiveEntrypoint('contao-encore-bundle');
        $frontendAsset->addActiveEntrypoint(new Entry('contao-slick-bundle', 'origin', 'Extension'));

        $this->assertTrue($frontendAsset->isActiveEntrypoint('contao-encore-bundle'));
        $this->assertTrue($frontendAsset->isActiveEntrypoint('contao-slick-bundle'));
        $this->assertFalse($frontendAsset->isActiveEntrypoint('contao-missing-bundle'));
        $this->assertSame([
            'contao-encore-bundle' => 'contao-encore-bundle',
            'contao-slick-bundle' => 'contao-slick-bundle',
        ], $frontendAsset->getActiveEntrypoints());

        $this->assertTrue($responseContext->has(EntryBag::class));

        /** @var EntryBag $bag */
        $bag = $responseContext->get(EntryBag::class);
        $this->assertSame('origin', $bag->getEntry('contao-slick-bundle')?->origin);
        $this->assertSame('Extension', $bag->getEntry('contao-slick-bundle')?->extension);
    }

    public function testEntrypointsWithoutResponseContext(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $frontendAsset = new FrontendAsset(new ResponseContextAccessor($requestStack));
        $frontendAsset->addActiveEntrypoint('contao-encore-bundle');

        $this->assertFalse($frontendAsset->isActiveEntrypoint('contao-encore-bundle'));
        $this->assertSame([], $frontendAsset->getActiveEntrypoints());
    }
}

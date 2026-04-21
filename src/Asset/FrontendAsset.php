<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use HeimrichHannot\EncoreBundle\Request\ResponseContext\Entry;
use HeimrichHannot\EncoreBundle\Request\ResponseContext\EntryBag;

class FrontendAsset
{
    public function __construct(
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    /**
     * Add an active entrypoint.
     */
    public function addActiveEntrypoint(string|Entry $entrypoint): void
    {
        $bag = $this->getBag();
        if (!$bag) {
            return;
        }

        if (is_string($entrypoint)) {
            $entrypoint = new Entry($entrypoint, __METHOD__);
        }

        $bag->addEntry($entrypoint);
    }

    /**
     * Return a list of all active entrypoints.
     *
     * @return string[]
     */
    public function getActiveEntrypoints(): array
    {
        $bag = $this->getBag();
        if (!$bag) {
            return [];
        }

        return array_map(
            static fn (Entry $entry) => $entry->name,
            $bag->all()
        );
    }

    /**
     * Check if an entrypoint is set as active entrypoint.
     */
    public function isActiveEntrypoint(string $entrypoint): bool
    {
        $bag = $this->getBag();
        if (!$bag) {
            return false;
        }

        return null !== $bag->getEntry($entrypoint);
    }

    private function getBag(): ?EntryBag
    {
        $context = $this->responseContextAccessor->getResponseContext();
        if (!$context) {
            return null;
        }
        if (!$context->has(EntryBag::class)) {
            $context->add(new EntryBag());
        }

        /** @var EntryBag $bag */
        $bag = $context->get(EntryBag::class);

        return $bag;
    }
}

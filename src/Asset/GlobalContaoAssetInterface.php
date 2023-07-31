<?php

namespace HeimrichHannot\EncoreBundle\Asset;

interface GlobalContaoAssetInterface
{
    public function cleanGlobalArrayFromConfiguration(): void;

    public function cleanFromGlobalArray(string $key, array $entries): void;

    public function cleanJsAssets(array $entries): void;

    public function cleanJqueryAssets(array $entries): void;

    public function cleanCssAssets(array $entries): void;

    public function removeJqueryAsset(): void;
}
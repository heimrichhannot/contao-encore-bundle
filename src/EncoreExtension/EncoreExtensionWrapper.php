<?php

namespace HeimrichHannot\EncoreBundle\EncoreExtension;

use Composer\InstalledVersions;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EncoreExtensionWrapper
{
    private BundleInterface $bundle;
    private \ReflectionClass $reflection;
    private string $bundlePath;

    public function __construct(
        public readonly EncoreExtensionInterface $extension,
        private readonly KernelInterface $kernel,
    ) {
    }

    private function getBundle(): BundleInterface
    {
        if (!isset($this->bundle)) {
            $this->bundle = $this->kernel->getBundles()[$this->getBundleShortName()];
        }

        return $this->bundle;
    }

    private function getReflection(): \ReflectionClass
    {
        if (!isset($this->reflection)) {
            $this->reflection = new \ReflectionClass($this->extension->getBundle());
        }

        return $this->reflection;
    }

    public function getBundlePath(): string
    {
        if (!isset($this->bundlePath)) {
            if ('App' === $this->extension->getBundle()) {
                return '.';
            }

            $bundlePath = $this->getBundle()->getPath();
            if (!file_exists($bundlePath . \DIRECTORY_SEPARATOR . 'composer.json')) {
                $bundlePath = $bundlePath . \DIRECTORY_SEPARATOR . '..';
            }
            if (!file_exists($bundlePath . \DIRECTORY_SEPARATOR . 'composer.json')) {
                throw new \RuntimeException('[Encore Bundle] Could not find composer.json file for ' . $this->getBundle()->getName() . '. Skipping EncoreExtension ' . $this->extension::class . '.');
            }

            try {
                $composerData = json_decode(file_get_contents($bundlePath . '/composer.json'), null, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new \JsonException('composer.json of ' . $this->getBundleShortName() . ' has a syntax error.');
            }

            $bundlePath = InstalledVersions::getInstallPath($composerData->name);

            $this->bundlePath = rtrim((new Filesystem())->makePathRelative($bundlePath, $this->kernel->getProjectDir()), \DIRECTORY_SEPARATOR);
        }

        return $this->bundlePath;
    }

    public function getBundleShortName(): string
    {
        if ('App' === $this->extension->getBundle()) {
            return 'App';
        }
        return $this->getReflection()->getShortName();
    }
}

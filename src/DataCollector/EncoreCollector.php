<?php

namespace HeimrichHannot\EncoreBundle\DataCollector;

use Composer\InstalledVersions;
use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EncoreCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly ExtensionCollection $extensionCollection,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        if ($request->attributes->has('encore_entries')) {
            $entryPoints = $request->attributes->get('encore_entries');
            if (!($entryPoints instanceof EntryPoints)) {
                $this->data['enabled'] = false;

                return;
            }

            $this->data['entries'] = $entryPoints->all();
            $this->data['enabled'] = true;
        } else {
            $this->data['enabled'] = false;
        }

        $extensions = $this->extensionCollection->getExtensions();
        $extensionEntries = [];
        foreach ($extensions as $extension) {
            $reflection = new \ReflectionClass($extension->getBundle());
            $extensionEntries[] = [
                'name' => $reflection->getShortName(),
                'entries' => $extension->getEntries(),
            ];
        }

        $this->data['extensions'] = $extensionEntries;
    }

    public static function getTemplate(): ?string
    {
        return '@Contao/data_collector/huh_encore.html.twig';
    }

    public function isEnabled(): bool
    {
        return $this->data['enabled'] ?? false;
    }

    public function getEntries(): array
    {
        return $this->data['entries'] ?? [];
    }

    public function getVersion(): string
    {
        return InstalledVersions::getPrettyVersion('heimrichhannot/contao-encore-bundle');
    }

    public function getExtensions(): array
    {
        return $this->data['extensions'] ?? [];
    }
}

<?php

namespace HeimrichHannot\EncoreBundle\Test\DataCollector;

use Composer\InstalledVersions;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreBundle\DataCollector\EncoreCollector;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoint;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use HeimrichHannot\EncoreBundle\HeimrichHannotEncoreBundle;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EncoreCollectorTest extends ContaoTestCase
{
    public function testCollectWithEntryPoints(): void
    {
        $entryPoints = new EntryPoints();
        $entryPoints->add(new EntryPoint('app', head: true, requiresCss: true));
        $entryPoints->add(new EntryPoint('deferred', active: false));

        $extensionEntry = EncoreEntry::create('bundle-entry', '/build/bundle-entry.js');
        $extension = $this->createMock(EncoreExtensionInterface::class);
        $extension->expects($this->once())->method('getBundle')->willReturn(HeimrichHannotEncoreBundle::class);
        $extension->expects($this->once())->method('getEntries')->willReturn([$extensionEntry]);

        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->expects($this->once())->method('getExtensions')->willReturn([$extension]);

        $collector = new EncoreCollector($extensionCollection);

        $request = new Request();
        $request->attributes->set('encore_entries', $entryPoints);

        $collector->collect($request, new Response());

        $this->assertTrue($collector->isEnabled());
        $this->assertSame($entryPoints->all(), $collector->getEntries());
        $this->assertSame(
            [
                [
                    'name' => 'HeimrichHannotEncoreBundle',
                    'entries' => [$extensionEntry],
                ],
            ],
            $collector->getExtensions()
        );
        $this->assertSame('@Contao/data_collector/huh_encore.html.twig', EncoreCollector::getTemplate());
        $this->assertSame(
            InstalledVersions::getPrettyVersion('heimrichhannot/contao-encore-bundle'),
            $collector->getVersion()
        );
    }

    public function testCollectWithoutEntryPointsDisablesCollector(): void
    {
        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->expects($this->once())->method('getExtensions')->willReturn([]);

        $collector = new EncoreCollector($extensionCollection);
        $collector->collect(new Request(), new Response());

        $this->assertFalse($collector->isEnabled());
        $this->assertSame([], $collector->getEntries());
        $this->assertSame([], $collector->getExtensions());
    }

    public function testCollectWithInvalidEntryPointsAttributeDisablesCollector(): void
    {
        $extensionCollection = $this->createMock(ExtensionCollection::class);
        $extensionCollection->expects($this->never())->method('getExtensions');

        $collector = new EncoreCollector($extensionCollection);

        $request = new Request();
        $request->attributes->set('encore_entries', 'invalid');

        $collector->collect($request, new Response());

        $this->assertFalse($collector->isEnabled());
        $this->assertSame([], $collector->getEntries());
        $this->assertSame([], $collector->getExtensions());
    }
}

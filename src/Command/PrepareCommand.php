<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Command;

use Composer\InstalledVersions;
use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class PrepareCommand extends Command
{
    protected static $defaultName = 'huh:encore:prepare';
    protected static $defaultDescription = 'Does the necessary preparation for contao encore bundle. Needs to be called after changes to bundle encore entries.';

    private SymfonyStyle           $io;
    private CacheItemPoolInterface $encoreCache;
    private KernelInterface        $kernel;
    private array                  $bundleConfig;
    private Environment            $twig;
    private ExtensionCollection    $extensionCollection;

    public function __construct(CacheItemPoolInterface $encoreCache, KernelInterface $kernel, array $bundleConfig, Environment $twig, ExtensionCollection $extensionCollection)
    {
        parent::__construct();

        $this->encoreCache = $encoreCache;
        $this->kernel = $kernel;
        $this->bundleConfig = $bundleConfig;
        $this->twig = $twig;
        $this->extensionCollection = $extensionCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setAliases(['encore:prepare'])
            ->setDescription(static::$defaultDescription)
            ->addOption('skip-entries', null, InputOption::VALUE_OPTIONAL, 'Add a comma separated list of entries to skip their generation.', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $resultFile = $this->kernel->getProjectDir().\DIRECTORY_SEPARATOR.'encore.bundles.js';

        $skipEntries = $input->getOption('skip-entries') ? explode(',', $input->getOption('skip-entries')) : [];

        $this->io->text('Using <fg=green>'.$this->kernel->getEnvironment().'</> environment. (Use --env=[ENV] to change environment. See --help for more information!)');

        @unlink($resultFile);

        $this->encoreCache->clear();

        // js
        if (isset($this->bundleConfig['js_entries']) && \is_array($this->bundleConfig['js_entries'])) {
            // entries
            $entries = [];

            foreach ($this->bundleConfig['js_entries'] as $entry) {
                $preparedEntry = [
                    'name' => $entry['name'],
                ];

                if (!str_starts_with($entry['file'], '@')) {
                    $preparedEntry['file'] = './'.preg_replace('@^\.?\/@i', '', $entry['file']);
                } else {
                    $preparedEntry['file'] = rtrim((new Filesystem())->makePathRelative($this->kernel->locateResource($entry['file']), $this->kernel->getProjectDir()), \DIRECTORY_SEPARATOR);
                }
                $entries[] = $preparedEntry;
            }

            foreach ($this->extensionCollection->getExtensions() as $extension) {
                $reflection = new \ReflectionClass($extension::getBundle());
                $bundle = $this->kernel->getBundles()[$reflection->getShortName()];
                $bundlePath = $bundle->getPath();
                if (!str_starts_with($bundlePath, $this->kernel->getProjectDir())) {
                    if (!file_exists($bundlePath.'/composer.json')) {
                        trigger_error(
                            '[Encore Bundle] Bundle '.$bundle->getName()
                            .' seems to be symlinked, but a composer.json file could not be found.'
                            .' Skipping EncoreExtension '.\get_class($extension).'.'
                        );
                        continue;
                    }

                    $composerData = json_decode(file_get_contents($bundlePath.'/composer.json'));
                    $bundlePath = InstalledVersions::getInstallPath($composerData->name);
                }

                $bundlePath = rtrim((new Filesystem())->makePathRelative($bundlePath, $this->kernel->getProjectDir()), \DIRECTORY_SEPARATOR);

                $preparedEntry = [];
                foreach ($extension::getEntries() as $entry) {
                    $preparedEntry['name'] = $entry->getName();
                    $preparedEntry['file'] = $bundlePath.\DIRECTORY_SEPARATOR.ltrim($entry->getPath(), \DIRECTORY_SEPARATOR);
                    $entries[] = $preparedEntry;
                }
            }

            $content = $this->twig->render('@HeimrichHannotContaoEncore/encore_bundles.js.twig', [
                'entries' => $entries,
                'skipEntries' => $skipEntries,
            ]);

            $result = file_put_contents($resultFile, $content);

            $this->io->success('Created encore.bundles.js in your project root. You can now require it in your webpack.config.js!');
        } else {
            $this->io->warning('No entries found in yml config huh.encore.entries -> No encore.bundles.js is created.');
        }

        return 0;
    }
}

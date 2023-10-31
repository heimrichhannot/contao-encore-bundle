<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Command;

use Composer\InstalledVersions;
use const DIRECTORY_SEPARATOR;
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
    public const DEPENDENCY_PREFIX = '@huh/encore-bundle--';

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

        $this->io->title('Update project encore data');

        $resultFile = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.'encore.bundles.js';

        $skipEntries = $input->getOption('skip-entries') ? explode(',', $input->getOption('skip-entries')) : [];

        $this->io->writeln('Using <fg=green>'.$this->kernel->getEnvironment().'</> environment. (Use --env=[ENV] to change environment. See --help for more information!)');

        @unlink($resultFile);

        $this->encoreCache->clear();

        $this->io->text(['', ' // Collect entries', '']);

        $this->io->writeln('> Collect entries from yaml config');

        $encoreJsEntries = [];

        $this->io->writeln(['', '> Collect entries from encore extensions']);
        $extensionDependencies = [];
        $extensionList = [];

        foreach ($this->extensionCollection->getExtensions() as $extension) {
            $reflection = new \ReflectionClass($extension->getBundle());
            $bundle = $this->kernel->getBundles()[$reflection->getShortName()];
            $bundlePath = $bundle->getPath();
            if (!file_exists($bundlePath.DIRECTORY_SEPARATOR.'composer.json')) {
                $bundlePath = $bundlePath.DIRECTORY_SEPARATOR.'..';
            }
            if (!file_exists($bundlePath.DIRECTORY_SEPARATOR.'composer.json')) {
                trigger_error(
                    '[Encore Bundle] Could not find composer.json file for '.$bundle->getName().'.'
                    .' Skipping EncoreExtension '.\get_class($extension).'.'
                );
                continue;
            }

            try {
                $composerData = json_decode(file_get_contents($bundlePath.'/composer.json'), null, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \JsonException('composer.json of '.$reflection->getShortName().' has a syntax error.');
            }

            $bundlePath = InstalledVersions::getInstallPath($composerData->name);

            $bundlePath = rtrim((new Filesystem())->makePathRelative($bundlePath, $this->kernel->getProjectDir()), DIRECTORY_SEPARATOR);

            $preparedEntry = [];
            foreach ($extension->getEntries() as $entry) {
                $preparedEntry['name'] = $entry->getName();
                $preparedEntry['file'] = '.'.DIRECTORY_SEPARATOR.$bundlePath.DIRECTORY_SEPARATOR.ltrim($entry->getPath(), DIRECTORY_SEPARATOR);
                $encoreJsEntries[] = $preparedEntry;
            }

            if (file_exists($bundlePath.DIRECTORY_SEPARATOR.'package.json')) {
                $packageData = json_decode(file_get_contents($bundlePath.DIRECTORY_SEPARATOR.'package.json'), true);
                $extensionDependencies = array_merge($extensionDependencies, ($packageData['dependencies'] ?? []));
            }

            $extensionList[] = [$reflection->getShortName(), \get_class($extension), $bundlePath];
        }

        $this->io->newLine();
        $this->io->writeln('Found <fg=green>'.\count($this->extensionCollection->getExtensions()).'</> registered encore extensions.');

        if ($this->io->isVerbose()) {
            $this->io->table(['Bundle', 'Extension', 'Bundle path'], $extensionList);
        }

        $this->io->text(['', ' // Update encore entry dependencies', '']);

        $projectPackageJsonPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.'package.json';
        if (!file_exists($projectPackageJsonPath)) {
            throw new \Exception('No package.json could be found in your project. This file must be present for encore to work!');
        }

        $this->io->writeln('Collect encore entry dependencies ');
        $encorePackageData = [
            'name' => '@hundh/encore-entry-dependencies',
            'version' => date('Ymd').'.'.date('Hi').'.'.time(),
            'dependencies' => $extensionDependencies,
        ];
        $encoreAssetsPath = 'vendor'.DIRECTORY_SEPARATOR.'heimrichhannot'.DIRECTORY_SEPARATOR.'encore-entry-dependencies';

        (new Filesystem())->dumpFile(
            $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.$encoreAssetsPath.DIRECTORY_SEPARATOR.'package.json',
            json_encode($encorePackageData, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );

        $this->io->writeln('Register dependencies in project');
        $packageData = json_decode(file_get_contents($projectPackageJsonPath), true, 512, \JSON_THROW_ON_ERROR);

        $packageData['dependencies'] = array_merge(
            ['@hundh/encore-entry-dependencies' => 'file:.'.DIRECTORY_SEPARATOR.$encoreAssetsPath.DIRECTORY_SEPARATOR],
            $packageData['dependencies'] ?? []
        );

        (new Filesystem())->dumpFile(
            $projectPackageJsonPath,
            json_encode($packageData, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );

        if (!empty($encoreJsEntries)) {
            $this->io->text(['', ' // Output encore_bundles.js', '']);

            $content = $this->twig->render('@HeimrichHannotEncore/encore_bundles.js.twig', [
                'entries' => $encoreJsEntries,
                'skipEntries' => $skipEntries,
            ]);

            file_put_contents($resultFile, $content);

            $this->io->writeln('Created encore.bundles.js in your project root. You can now require it in your webpack.config.js');
        } else {
            $this->io->warning('Found no registered encore entries. Skipped encore.bundles.js creation.');
        }

        $this->io->success('Finished updating your project encore data.');

        $this->io->text([
            'Next steps:',
            '1. If your dependencies have changed, run <fg=black;bg=cyan> yarn upgrade </>.',
            '2. Compile your asset with <fg=black;bg=cyan> yarn encore dev </>, <fg=black;bg=cyan> yarn encore dev --watch </> or <fg=black;bg=cyan> yarn encore prod </>',
            '',
        ]);

        return 0;
    }
}

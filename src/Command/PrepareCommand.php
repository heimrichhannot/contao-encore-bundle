<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PrepareCommand extends AbstractLockedCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $rootDir;
    /**
     * @var PhpArrayAdapter
     */
    private $encoreCache;
    /**
     * @var array
     */
    private $bundleConfig;

    public function __construct(array $bundleConfig, CacheItemPoolInterface $encoreCache)
    {
        $this->encoreCache = $encoreCache;

        parent::__construct();
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('encore:prepare')
            ->setDescription('Does the necessary preparation for contao encore bundle. Needs to be called only after adding new webpack entries in your yml files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $twig = $this->getContainer()->get('twig');
        $resultFile = $this->rootDir.\DIRECTORY_SEPARATOR.'encore.bundles.js';

        $this->io->text('Using <fg=green>'.$this->getContainer()->getParameter('kernel.environment').'</> environment. (Use --env=[ENV] to change environment. See --help for more information!)');

        @unlink($resultFile);

        $this->encoreCache->clear();

        // js
        if (isset($this->bundleConfig['entries']) && \is_array($this->bundleConfig['entries'])) {
            // entries
            $entries = [];

            foreach ($this->bundleConfig['entries'] as $entry) {
                $preparedEntry = [
                    'name' => $entry['name'],
                ];

                if (!$this->getContainer()->get('huh.utils.string')->startsWith($entry['file'], '@')) {
                    $preparedEntry['file'] = './'.preg_replace('@^\.?\/@i', '', $entry['file']);
                } else {
                    $preparedEntry['file'] = $this->getContainer()->get('file_locator')->locate($entry['file']);
                }
                $entries[] = $preparedEntry;
            }

            $content = $twig->render('@HeimrichHannotContaoEncore/encore_bundles.js.twig', [
                'entries' => $entries,
            ]);

            file_put_contents($resultFile, $content);

            $this->io->success('Created encore.bundles.js in your project root. You can now require it in your webpack.config.js!');
        } else {
            $this->io->warning('No entries found in yml config huh.encore.entries -> No encore.bundles.js is created.');
        }

        return 0;
    }
}

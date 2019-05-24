<?php

namespace HeimrichHannot\EncoreBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
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
     * @var TagAwareAdapterInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $encoreCache, TagAwareAdapterInterface $cache)
    {

        $this->encoreCache = $encoreCache;

        parent::__construct();
        $this->cache = $cache;
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
        $this->io      = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $twig          = $this->getContainer()->get('twig');
        $resultFile    = $this->rootDir . DIRECTORY_SEPARATOR . 'encore.bundles.js';

        $this->io->text("Using <fg=green>".$this->getContainer()->getParameter('kernel.environment').'</> environment. (Use --env=[ENV] to change environment. See --help for mor information!)');

        @unlink($resultFile);

        $this->encoreCache->clear();
        $this->cache->clear();

        $config = $this->getContainer()->getParameter('huh.encore');

        // js
        if (isset($config['encore']['entries']) && is_array($config['encore']['entries'])) {
            // entries
            $entries = [];

            foreach ($config['encore']['entries'] as $entry) {
                $preparedEntry = [
                    'name' => $entry['name']
                ];

                $preparedEntry['file'] = './' . preg_replace('@^\.?\/@i', '', $entry['file']);

                $entries[] = $preparedEntry;
            }

            $content = $twig->render('@HeimrichHannotContaoEncore/encore_bundles.js.twig', [
                'entries'       => $entries
            ]);

            file_put_contents($resultFile, $content);

            $this->io->success('Created encore.bundles.js in your project root. You can now require it in your webpack.config.js!');
        } else {
            $this->io->warning('No entries found in yml config huh.encore.entries -> No encore.bundles.js is created.');
        }

        return 0;
    }
}
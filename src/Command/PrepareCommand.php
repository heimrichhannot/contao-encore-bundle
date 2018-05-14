<?php

namespace HeimrichHannot\EncoreBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('encore:prepare')->setDescription(
            'Does the necessary preparation for contao encore bundle. Needs to be called only after adding new webpack entries in your yml files.'
        );
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

        echo PHP_EOL;

        @unlink($resultFile);

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
//            echo 'Warning: No encore.bundles.js is created.' . PHP_EOL . PHP_EOL;
        }

        return 0;
    }
}
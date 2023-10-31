<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Migration;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\LayoutModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;

class Version0100Migration implements MigrationInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var ContaoFramework
     */
    protected $framework;

    public function __construct(Connection $connection, ContaoFramework $framework)
    {
        $this->connection = $connection;
        $this->framework = $framework;
    }

    public function getName(): string
    {
        return 'Encore Bundle 1.0 Migration';
    }

    public function shouldRun(): bool
    {
        if (false === $this->connection->getSchemaManager()->tablesExist('tl_page')) {
            return false;
        }

        if (!\array_key_exists('addencore', $this->connection->getSchemaManager()->listTableColumns('tl_page'))) {
            return false;
        }
        if (!\array_key_exists('addencore', $this->connection->getSchemaManager()->listTableColumns('tl_layout'))) {
            return true;
        }

        return false;
    }

    public function run(): MigrationResult
    {
        if (!$this->framework->isInitialized()) {
            $this->framework->initialize();
        }

        Controller::loadDataContainer('tl_layout');

        $encoreFields = ['addEncore', 'addEncoreBabelPolyfill', 'encoreBabelPolyfillEntryName', 'encoreStylesheetsImportsTemplate', 'encoreScriptsImportsTemplate'];

        foreach ($encoreFields as $field) {
            if (!\array_key_exists(strtolower($field), $this->connection->getSchemaManager()->listTableColumns('tl_layout'))) {
                if (isset($GLOBALS['TL_DCA']['tl_layout']['fields'][$field]['sql'])) {
                    $this->connection->executeQuery('ALTER TABLE tl_layout ADD '.$field.' '.$GLOBALS['TL_DCA']['tl_layout']['fields'][$field]['sql']);
                }
            }
        }

        $pagesWithLayout = PageModel::findByIncludeLayout('1');
        $processedLayouts = [];

        foreach ($pagesWithLayout as $page) {
            if (\in_array($page->layout, $processedLayouts, true)) {
                continue;
            }

            $layout = LayoutModel::findByPk($page->layout);
            if (!$layout) {
                continue;
            }

            if ('root' !== $page->type) {
                $rootPage = PageModel::findByPk($page->rootId);
                if (!$rootPage) {
                    continue;
                }
            } else {
                $rootPage = $page;
            }

            if ($rootPage->addEncore) {
                reset($encoreFields);
                $updateString = implode('=?, ', $encoreFields).'=?';
                $values = [];
                foreach ($encoreFields as $field) {
                    $values[] = $rootPage->{$field};
                }

                $this->connection->executeQuery('UPDATE tl_layout SET '.$updateString, $values);
                $processedLayouts[] = $layout->id;
            }
        }

        return new MigrationResult(true, 'Encore Bundle successfully migrated!');
    }
}

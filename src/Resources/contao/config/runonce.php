<?php /** @noinspection ALL */

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

use Contao\Controller;
use Contao\Database;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EncoreBundleMigration
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Database
     */
    protected $database;

    public function __construct() {
        $this->container = System::getContainer();
        $this->database = $this->container->get('contao.framework')->createInstance(Database::class);
    }

    public function run()
    {
        // Don't run on fresh install
        if (!$this->database->tableExists('tl_page'))
        {
            return;
        }
        $this->migrationTo090();
    }

    protected function migrationTo090()
    {
        // tl_page.addEncore was deleted on version 0.9.0
        if (!$this->database->fieldExists('addEncore', 'tl_page'))
        {
            return;
        }

        Controller::loadDataContainer('tl_layout');

        $encoreFields = ['addEncore', 'encorePublicPath','addEncoreBabelPolyfill','encoreBabelPolyfillEntryName','encoreStylesheetsImportsTemplate','encoreScriptsImportsTemplate'];

        foreach ($encoreFields as $field)
        {
            if (!$this->database->fieldExists($field, 'tl_layout'))
            {
                if (isset($GLOBALS['TL_DCA']['tl_layout']['fields'][$field]['sql']))
                {
                    $this->database->execute("ALTER TABLE tl_layout ADD ".$field . " " . $GLOBALS['TL_DCA']['tl_layout']['fields'][$field]['sql']);
                }
            }
        }


        $pagesWithLayout = PageModel::findByIncludeLayout("1");
        $processedLayouts = [];

        foreach ($pagesWithLayout as $page)
        {
            if (in_array($page->layout, $processedLayouts))
            {
                continue;
            }

            $layout = LayoutModel::findByPk($page->layout);
            if (!$layout)
            {
                continue;
            }

            if (!"root" === $page->type)
            {
                $rootPage = PageModel::findByPk($page->rootId);
                if (!$rootPage)
                {
                    continue;
                }
            }
            else {
                $rootPage = $page;
            }

            if ($rootPage->addEncore)
            {
                reset($encoreFields);
                foreach ($encoreFields as $field) {
                    $layout->{$field} = $rootPage->{$field};
                }
            }
        }
    }
}

$migration = new EncoreBundleMigration();
$migration->run();
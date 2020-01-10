<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\DependencyInjection;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends ContaoTestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertCount(5, $treeBuilder->getRootNode()->getChildNodeDefinitions());

        $tree = $treeBuilder->buildTree();
        $this->assertSame('huh_encore',$tree->getName());
        $this->assertTrue($tree->getChildren()['encore']->isDeprecated());


    }
}
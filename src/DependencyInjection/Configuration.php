<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('huh_encore');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('imports')
                            ->info('Register import templates to customize how assets are imported into your templates.')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->info('Unique template alias. Example: default_css')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('template')
                                        ->info('Full references twig template path. Example: @HeimrichHannotContaoEncore/encore_css_imports.html.twig')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('unset_jquery')
                    ->info('Remove jQuery from global array, if addJQuery is enabled in layout section.')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @todo Remove in version 2.0
     */
    public function addLegacyNode()
    {
        $treeBuilder = new TreeBuilder('encore');
        $node = $treeBuilder->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->setDeprecated('Configs within encore key are deprecated and will be removed in next major version.')
            ->children()
                ->arrayNode('entries')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('file')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode('requiresCss')->end()
                            ->booleanNode('head')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('imports')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('template')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('legacy')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('js')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('jquery')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('css')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}

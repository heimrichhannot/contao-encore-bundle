<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('huh_encore');

        $rootNode
            ->children()
                ->arrayNode('encore')
                    ->addDefaultsIfNotSet()
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
                                    ->booleanNode('requiresCss')
                                    ->end()
                                    ->booleanNode('head')
                                    ->end()
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
                ->end()
            ->end();

        return $treeBuilder;
    }
}

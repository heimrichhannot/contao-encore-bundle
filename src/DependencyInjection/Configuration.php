<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('huh_encore');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

        $rootNode
            ->children()
                ->booleanNode('use_contao_template_variables')
                    ->defaultFalse()
                    ->info('Use contao template variables in fe_page (stylesheets, head, mootools) for inserting assets instead of the custom template variables of this bundle. Recommended: true. Default: false (due bc reasons). Will be default true in next major version.')
                ->end()
                ->arrayNode('js_entries')
                    ->info('Add javascript files which should be registered as encore entries.')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->info('Will be shown in contao backend and will be used as alias/identifier in the database.')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('file')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Path to the Javascript file.')
                            ->end()
                            ->booleanNode('requires_css')
                                ->info('Set to true, if entry requires css.')
                            ->end()
                            ->booleanNode('head')
                                ->info('Set to true, if entry should added to the encoreHeadScripts section in your page layout instead to the bottom (CSS will always be added to the head).')
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
                ->arrayNode('unset_global_keys')
                    ->info("A list of keys that should be stripped from the global contao arrays. Here you can add assets, that you serve with webpack, so they won't be loaded twice or on the wrong page. IMPORTANT: The strings defined here must match the array keys in Contao's global arrays")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('js')
                            ->scalarPrototype()->end()
                            ->info("Assets will be stripped from \$GLOBALS['TL_JAVASCRIPT']")
                        ->end()
                        ->arrayNode('jquery')
                            ->scalarPrototype()->end()
                            ->info("Assets will be stripped from \$GLOBALS['TL_JQUERY']")
                        ->end()
                        ->arrayNode('css')
                            ->scalarPrototype()->end()
                            ->info("Assets will be stripped from \$GLOBALS['TL_USER_CSS'] and \$GLOBALS['TL_CSS']")
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('unset_jquery')
                    ->info('Remove jQuery from global array, if addJQuery is enabled in layout section.')
                    ->defaultFalse()
                ->end()
                ->append($this->addLegacyNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @deprecated Will be removed in version 2.0
     */
    public function addLegacyNode()
    {
        $treeBuilder = new TreeBuilder('encore');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->root('encore');
        } else {
            $node = $treeBuilder->getRootNode();
        }

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

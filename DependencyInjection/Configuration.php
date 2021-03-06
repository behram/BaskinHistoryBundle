<?php

namespace Baskin\HistoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('baskin_history');

        $rootNode
            ->children()
                ->scalarNode('template')->defaultValue('BaskinHistoryBundle:History:history.html.twig')->end()
                ->scalarNode('versionParameter')->defaultValue('version')->end()
                ->booleanNode('revert')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

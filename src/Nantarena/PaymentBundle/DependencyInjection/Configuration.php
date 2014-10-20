<?php

namespace Nantarena\PaymentBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nantarena_payment');

        $rootNode
            ->children()
                ->arrayNode('paypal')
                    ->children()
                        ->arrayNode('credentials')
                            ->children()
                                ->scalarNode('clientid')->defaultValue('~')->end()
                                ->scalarNode('secret')->defaultValue('~')->end()
                            ->end()
                        ->end()
                        ->arrayNode('service')
                            ->children()
                                ->scalarNode('http_connection_timeout')->defaultValue('30')->end()
                                ->scalarNode('http_retry')->defaultValue('1')->end()
                                ->scalarNode('http_proxy')
                                    ->defaultValue('~')
                                    ->example('http://[username:password]@hostname[:port][/path]')
                                    ->end()
                                ->scalarNode('mode')
                                    ->defaultValue('sandbox')
                                    ->info('live or sandbox mode')
                                    ->end()
                                ->scalarNode('log_enable')->defaultValue('true')->end()
                                ->scalarNode('log_file')->defaultValue('../PayPal.log')->end()
                                ->scalarNode('log_level')
                                    ->defaultValue('FINE')
                                    ->info('in verbose ordor FINE(max), INFO, WARN or ERROR(min)')
                                    ->end() 
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

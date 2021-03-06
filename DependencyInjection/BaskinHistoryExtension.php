<?php

namespace Baskin\HistoryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BaskinHistoryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->register(
            'baskin.history.twig_extension',
            'Baskin\\HistoryBundle\\Twig\\HistoryExtension'
        )
            ->addArgument(new Reference('doctrine'))
            ->addArgument(new Reference('twig'))
            ->addArgument(new Reference('stof_doctrine_extensions.listener.loggable'))
            ->addArgument($config['template'])
            ->addArgument($config['versionParameter'])
            ->addArgument($config['revert'])
            ->addTag('twig.extension');

        if ($config['revert']) {
            $container->register(
                'baskin.history.reverter',
                'Baskin\\HistoryBundle\\Service\\Reverter'
            )
                ->addArgument(new Reference('doctrine'))
                ->addArgument(new Reference('request_stack'))
                ->addArgument(new Reference('stof_doctrine_extensions.listener.loggable'))
                ->addArgument($config['versionParameter']);

            $container->setAlias('reverter', 'baskin.history.reverter');

            if (class_exists('Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter')) {
                $container->register(
                    'baskin.history.param_converter.doctrine',
                    'Baskin\\HistoryBundle\\ParamConverter\\HistoryParamConverter'
                )
                    ->addArgument(new Reference('reverter'))
                    ->addArgument(new Reference('doctrine'))
                    ->addTag('request.param_converter', array('converter' => 'doctrine.orm', 'priority' => 1));
            }
        }
    }
}

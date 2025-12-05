<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContentAdditionalInformationsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $ibexaOrmConfig = [
            'orm' => [
                'entity_mappings' => [
                    'ContentAdditionalInformations' => [
                        'type' => 'annotation',
                        'dir' => __DIR__ . '/../../lib',
                        'prefix' => 'ErdnaxelaWeb\ContentAdditionalInformations',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ];
        $container->prependExtensionConfig('ibexa', $ibexaOrmConfig);
    }
}

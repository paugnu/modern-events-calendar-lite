<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;

class TranslatorPass implements CompilerPassInterface
{
    public function __construct(private readonly string $translatorServiceId = 'translator.default', private readonly string $readerServiceId = 'translation.reader', private readonly string $loaderTag = 'translation.loader', private readonly string $debugCommandServiceId = 'console.command.translation_debug', private readonly string $updateCommandServiceId = 'console.command.translation_update')
    {
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->translatorServiceId)) {
            return;
        }

        $loaders = [];
        $loaderRefs = [];
        foreach ($container->findTaggedServiceIds($this->loaderTag, true) as $id => $attributes) {
            $loaderRefs[$id] = new Reference($id);
            $loaders[$id][] = $attributes[0]['alias'];
            if (isset($attributes[0]['legacy-alias'])) {
                $loaders[$id][] = $attributes[0]['legacy-alias'];
            }
        }

        if ($container->hasDefinition($this->readerServiceId)) {
            $definition = $container->getDefinition($this->readerServiceId);
            foreach ($loaders as $id => $formats) {
                foreach ($formats as $format) {
                    $definition->addMethodCall('addLoader', [$format, $loaderRefs[$id]]);
                }
            }
        }

        $container
            ->findDefinition($this->translatorServiceId)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $loaderRefs))
            ->replaceArgument(3, $loaders)
        ;

        if (!$container->hasParameter('twig.default_path')) {
            return;
        }

        if ($container->hasDefinition($this->debugCommandServiceId)) {
            $container->getDefinition($this->debugCommandServiceId)->replaceArgument(4, $container->getParameter('twig.default_path'));
        }

        if ($container->hasDefinition($this->updateCommandServiceId)) {
            $container->getDefinition($this->updateCommandServiceId)->replaceArgument(5, $container->getParameter('twig.default_path'));
        }
    }
}

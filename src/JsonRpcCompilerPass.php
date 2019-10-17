<?php


namespace JsonRpcServerBundle;


use JsonRpcServerBundle\Contract\MethodHandlerInterface;
use JsonRpcServerBundle\Service\MethodCollectionService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class JsonRpcCompilerPass implements CompilerPassInterface
{
    const TAG_NAME = 'json.rpc.server.method';

    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(MethodHandlerInterface::class)->addTag(self::TAG_NAME);//TODO find out why not working

        $collection = $container->findDefinition(MethodCollectionService::class);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $id => $tags) {
            $collection->addMethodCall('addMethod', [new Reference($id)]);
        }

    }
}

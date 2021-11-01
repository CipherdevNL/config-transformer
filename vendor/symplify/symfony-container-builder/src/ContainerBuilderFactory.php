<?php

declare (strict_types=1);
namespace ConfigTransformer202111013\Symplify\SymfonyContainerBuilder;

use ConfigTransformer202111013\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202111013\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use ConfigTransformer202111013\Symplify\SymfonyContainerBuilder\Contract\Config\LoaderFactoryInterface;
use ConfigTransformer202111013\Symplify\SymfonyContainerBuilder\DependencyInjection\LoadExtensionConfigsCompilerPass;
use ConfigTransformer202111013\Webmozart\Assert\Assert;
final class ContainerBuilderFactory
{
    /**
     * @var \Symplify\SymfonyContainerBuilder\Contract\Config\LoaderFactoryInterface
     */
    private $loaderFactory;
    public function __construct(\ConfigTransformer202111013\Symplify\SymfonyContainerBuilder\Contract\Config\LoaderFactoryInterface $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }
    /**
     * @param ExtensionInterface[] $extensions
     * @param CompilerPassInterface[] $compilerPasses
     * @param string[] $configFiles
     */
    public function create(array $extensions, array $compilerPasses, array $configFiles) : \ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder
    {
        \ConfigTransformer202111013\Webmozart\Assert\Assert::allString($configFiles);
        \ConfigTransformer202111013\Webmozart\Assert\Assert::allFile($configFiles);
        $containerBuilder = new \ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->registerExtensions($containerBuilder, $extensions);
        $this->registerConfigFiles($containerBuilder, $configFiles);
        $this->registerCompilerPasses($containerBuilder, $compilerPasses);
        // this calls load() method in every extensions
        // ensure these extensions are implicitly loaded
        $compilerPassConfig = $containerBuilder->getCompilerPassConfig();
        $compilerPassConfig->setMergePass(new \ConfigTransformer202111013\Symplify\SymfonyContainerBuilder\DependencyInjection\LoadExtensionConfigsCompilerPass());
        return $containerBuilder;
    }
    /**
     * @param ExtensionInterface[] $extensions
     */
    private function registerExtensions(\ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $extensions) : void
    {
        foreach ($extensions as $extension) {
            $containerBuilder->registerExtension($extension);
        }
    }
    /**
     * @param CompilerPassInterface[] $compilerPasses
     */
    private function registerCompilerPasses(\ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $compilerPasses) : void
    {
        foreach ($compilerPasses as $compilerPass) {
            $containerBuilder->addCompilerPass($compilerPass);
        }
    }
    /**
     * @param string[] $configFiles
     */
    private function registerConfigFiles(\ConfigTransformer202111013\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $configFiles) : void
    {
        $delegatingLoader = $this->loaderFactory->create($containerBuilder, \getcwd());
        foreach ($configFiles as $configFile) {
            $delegatingLoader->load($configFile);
        }
    }
}

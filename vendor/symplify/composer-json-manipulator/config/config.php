<?php

declare (strict_types=1);
namespace ConfigTransformer20210610;

use ConfigTransformer20210610\Symfony\Component\Console\Style\SymfonyStyle;
use ConfigTransformer20210610\Symfony\Component\DependencyInjection\ContainerInterface;
use ConfigTransformer20210610\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ConfigTransformer20210610\Symplify\ComposerJsonManipulator\ValueObject\Option;
use ConfigTransformer20210610\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use ConfigTransformer20210610\Symplify\PackageBuilder\Parameter\ParameterProvider;
use ConfigTransformer20210610\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use ConfigTransformer20210610\Symplify\SmartFileSystem\SmartFileSystem;
use function ConfigTransformer20210610\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\ConfigTransformer20210610\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\ConfigTransformer20210610\Symplify\ComposerJsonManipulator\ValueObject\Option::INLINE_SECTIONS, ['keywords']);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ConfigTransformer20210610\Symplify\ComposerJsonManipulator\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle']);
    $services->set(\ConfigTransformer20210610\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\ConfigTransformer20210610\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\ConfigTransformer20210610\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->args([\ConfigTransformer20210610\Symfony\Component\DependencyInjection\Loader\Configurator\service(\ConfigTransformer20210610\Symfony\Component\DependencyInjection\ContainerInterface::class)]);
    $services->set(\ConfigTransformer20210610\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\ConfigTransformer20210610\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\ConfigTransformer20210610\Symfony\Component\DependencyInjection\Loader\Configurator\service(\ConfigTransformer20210610\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
};

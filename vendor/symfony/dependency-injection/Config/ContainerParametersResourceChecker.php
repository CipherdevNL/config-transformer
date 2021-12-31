<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer2021123110\Symfony\Component\DependencyInjection\Config;

use ConfigTransformer2021123110\Symfony\Component\Config\Resource\ResourceInterface;
use ConfigTransformer2021123110\Symfony\Component\Config\ResourceCheckerInterface;
use ConfigTransformer2021123110\Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class ContainerParametersResourceChecker implements \ConfigTransformer2021123110\Symfony\Component\Config\ResourceCheckerInterface
{
    private $container;
    public function __construct(\ConfigTransformer2021123110\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * {@inheritdoc}
     */
    public function supports(\ConfigTransformer2021123110\Symfony\Component\Config\Resource\ResourceInterface $metadata) : bool
    {
        return $metadata instanceof \ConfigTransformer2021123110\Symfony\Component\DependencyInjection\Config\ContainerParametersResource;
    }
    /**
     * {@inheritdoc}
     */
    public function isFresh(\ConfigTransformer2021123110\Symfony\Component\Config\Resource\ResourceInterface $resource, int $timestamp) : bool
    {
        foreach ($resource->getParameters() as $key => $value) {
            if (!$this->container->hasParameter($key) || $this->container->getParameter($key) !== $value) {
                return \false;
            }
        }
        return \true;
    }
}

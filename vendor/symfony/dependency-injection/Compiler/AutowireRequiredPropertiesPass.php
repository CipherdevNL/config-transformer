<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer2021120710\Symfony\Component\DependencyInjection\Compiler;

use ConfigTransformer2021120710\Symfony\Component\DependencyInjection\ContainerInterface;
use ConfigTransformer2021120710\Symfony\Component\DependencyInjection\Definition;
use ConfigTransformer2021120710\Symfony\Component\DependencyInjection\TypedReference;
use ConfigTransformer2021120710\Symfony\Contracts\Service\Attribute\Required;
/**
 * Looks for definitions with autowiring enabled and registers their corresponding "@required" properties.
 *
 * @author Sebastien Morel (Plopix) <morel.seb@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class AutowireRequiredPropertiesPass extends \ConfigTransformer2021120710\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return mixed
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        $value = parent::processValue($value, $isRoot);
        if (!$value instanceof \ConfigTransformer2021120710\Symfony\Component\DependencyInjection\Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!($reflectionClass = $this->container->getReflectionClass($value->getClass(), \false))) {
            return $value;
        }
        $properties = $value->getProperties();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (!($type = null) instanceof \ReflectionNamedType) {
                continue;
            }
            if (![] && (\false === ($doc = $reflectionProperty->getDocComment()) || \false === \stripos($doc, '@required') || !\preg_match('#(?:^/\\*\\*|\\n\\s*+\\*)\\s*+@required(?:\\s|\\*/$)#i', $doc))) {
                continue;
            }
            if (\array_key_exists($name = $reflectionProperty->getName(), $properties)) {
                continue;
            }
            $type = $type->getName();
            $value->setProperty($name, new \ConfigTransformer2021120710\Symfony\Component\DependencyInjection\TypedReference($type, $type, \ConfigTransformer2021120710\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $name));
        }
        return $value;
    }
}

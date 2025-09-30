<?php

declare(strict_types=1);

namespace Symplify\ConfigTransformer\Converter;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symplify\ConfigTransformer\Routing\RoutingConfigDetector;
use Symplify\PhpConfigPrinter\NodeFactory\ContainerConfiguratorReturnClosureFactory;
use Symplify\PhpConfigPrinter\NodeFactory\RoutingConfiguratorReturnClosureFactory;
use Symplify\PhpConfigPrinter\Printer\PhpParserPhpConfigPrinter;
use Symplify\PhpConfigPrinter\Yaml\CheckerServiceParametersShifter;

/**
 * @api
 * @see \Symplify\ConfigTransformer\Tests\Converter\YamlToPhpConverter\YamlToPhpConverterTest
 */
final class XmlToPhpConverter
{
 private array $namespaceTable = [];

    public function __construct(
        private readonly PhpParserPhpConfigPrinter $phpParserPhpConfigPrinter,
        private readonly ContainerConfiguratorReturnClosureFactory $containerConfiguratorReturnClosureFactory,
        private readonly RoutingConfiguratorReturnClosureFactory $routingConfiguratorReturnClosureFactory,
        private readonly CheckerServiceParametersShifter $checkerServiceParametersShifter,
        private readonly RoutingConfigDetector $routingConfigDetector
    ) {
    }

    public function convert(string $xml, string $filePath): string
    {
        $dom = XmlUtils::parse($xml);

        $this->namespaceTable = [];

        $services = [];
        $instanceof = [];
        foreach ($dom->firstChild->childNodes as $elm) {
            /** @var \DOMElement $elm */
            if ($elm->nodeName !== 'services') {
                continue;
            }

            foreach ($elm->childNodes as $elm1) {
                /** @var \DOMElement $elm1 */

                if ($elm1->nodeName === 'defaults') {
                    $services = array_merge($services, $this->convertDefaultsNode($elm1));
                }

                if ($elm1->nodeName === 'prototype') {
                    $services = array_merge($services, $this->convertPrototypeNode($elm1));
                }

                if ($elm1->nodeName === 'service') {
                    $services = array_merge($services, $this->convertPrototypeNode($elm1));
                }

                if ($elm1->nodeName === 'instanceof') {
                    $instanceof = array_merge($instanceof, $this->convertPrototypeNode($elm1));
                }
            }
        }

        if ($instanceof !== []) {
            $services['_instanceof'] = $instanceof;
        }

        $php = $this->convertYamlArray(['services' => $services], $filePath);
        return preg_replace(
            "/service\\('\\?(.*)'/",
            "new \\\\Symfony\\\\Component\\\\DependencyInjection\\\\Reference('$1', \\\\Symfony\\\\Component\\\\DependencyInjection\\\\ContainerInterface::NULL_ON_INVALID_REFERENCE",
            $php
        );
    }

    /**
     * @param array<string, mixed> $yamlArray
     */
    public function convertYamlArray(array $yamlArray, string $filePath): string
    {
        if ($this->routingConfigDetector->isRoutingFilePath($filePath)) {
            $return = $this->routingConfiguratorReturnClosureFactory->createFromArrayData($yamlArray);
        } else {
            $yamlArray = $this->checkerServiceParametersShifter->process($yamlArray);
            $return = $this->containerConfiguratorReturnClosureFactory->createFromYamlArray($yamlArray);
        }

        return $this->phpParserPhpConfigPrinter->prettyPrintFile([$return]);
    }

    private function convertDefaultsNode(\DOMElement $elm1): array
    {
        $defaults = [];
        if( $elm1->hasAttribute('autowire')) {
            $defaults['autowire'] = (bool)$elm1->getAttribute('autowire');
        }

        if( $elm1->hasAttribute('autoconfigure')) {
            $defaults['autoconfigure'] = (bool)$elm1->getAttribute('autoconfigure');
        }

        if( $elm1->hasAttribute('public') && (bool)$elm1->getAttribute('public') === true) {
            $defaults['public'] = true;
        }

        $binds = [];
        /** @var \DOMElement $node */
        foreach ($elm1->childNodes as $node) {
            if ($node->nodeName !== 'bind') {
                continue;
            }

            $value = null;
            if ($node->getAttribute('type') === 'service') {
                $value = '@' . $node->getAttribute('id');
            } elseif ($node->hasAttribute('id')) {
                $value = $node->getAttribute('id');
            } else {
                $value = $node->nodeValue;
            }

            $binds[$node->getAttribute('key')] = $value;
        }

        if ($binds !== []) {
            $defaults['bind'] = $binds;
        }

        if ($defaults === []) {
            return [];
        }

        return ['_defaults' => $defaults];
    }

    private function convertPrototypeNode(\DOMElement $elm1): array
    {
        $prototype = [];
        $id = null;
        if ($elm1->hasAttribute('namespace')) {
            $id = $elm1->getAttribute('namespace');
            if (in_array($id, $this->namespaceTable)) {
                $prototype['namespace'] = $id;
                $id .= uniqid();
            }

            $this->namespaceTable[] = $id;
        }

        if ($elm1->hasAttribute('id')) {
            $id = $elm1->getAttribute('id');
        }

        if( $elm1->hasAttribute('resource')) {
            $prototype['resource'] = $elm1->getAttribute('resource');
        }

        if( $elm1->hasAttribute('class')) {
            $prototype['class'] = $elm1->getAttribute('class');
        }

        if( $elm1->hasAttribute('alias')) {
            $prototype['alias'] = $elm1->getAttribute('alias');
        }

        if( $elm1->hasAttribute('public') && (bool)$elm1->getAttribute('public') === true) {
            $prototype['public'] = true;
        }

        if( $elm1->hasAttribute('shared')) {
            $prototype['shared'] = $elm1->getAttribute('shared');
        }

        $tags = [];
        $arguments = [];
        $calls = [];
        /** @var \DOMElement $childNode */
        foreach ($elm1->childNodes as $childNode) {
            if ($childNode->nodeName === 'tag') {
                $tags[] = $this->convertTagNode($childNode);
            }

            if ($childNode->nodeName === 'factory') {
                $class = $childNode->getAttribute('class');
                if ($childNode->hasAttribute('service')) {
                    $class = '@' . $childNode->getAttribute('service');
                }

                $prototype['factory'] = [$class, $childNode->getAttribute('method')];
            }

            if ($childNode->nodeName === 'argument') {
                $arguments = array_merge($arguments, $this->convertArgumentNode($childNode));
            }

            if ($childNode->nodeName === 'call') {
                $calls[] = $this->convertCallNode($childNode);
            }
        }

        if ($arguments !== []) {
            $prototype['arguments'] = $arguments;
        }

        if ($calls !== []) {
            $prototype['calls'] = $calls;
        }

        if ($tags !== []) {
            $prototype['tags'] = $tags;
        }

        return [$id => $prototype];
    }

    private function convertTagNode(\DOMElement $childNode): array
    {
        $tag = [];
        /** @var \DOMAttr $attribute */
        foreach ($childNode->attributes as $attribute) {
            $tag[$attribute->nodeName] = $attribute->value;
        }

        return $tag;
    }

    private function convertArgumentNode(\DOMElement $childNode): array
    {
        $value = null;
        if ($childNode->getAttribute('type') === 'collection') {
            $value = [];
        } elseif ($childNode->getAttribute('type') === 'service') {
            $prefix = '@';
            if ($childNode->hasAttribute('on-invalid')) {
                $prefix .= '?';
            }
            $value = $prefix . $childNode->getAttribute('id');
        } elseif ($childNode->hasAttribute('id')) {
            $value = $childNode->getAttribute('id');
        } else {
            $value = $childNode->nodeValue;
        }

        if ($value === 'null') {
            $value = null;
        }

        if ($childNode->hasAttribute('key')) {
            return [$childNode->getAttribute('key') => $value];
        }

        return [$value];
    }

    private function convertCallNode(\DOMElement $node): array
    {
        $arguments = [];
        /** @var \DOMElement $cNode */
        foreach ($node->childNodes as $cNode) {
            if ($cNode->nodeName !== 'argument') {
                continue;
            }

            $arguments = array_merge($arguments, $this->convertArgumentNode($cNode));
        }

        return [$node->getAttribute('method') => $arguments];
    }
}

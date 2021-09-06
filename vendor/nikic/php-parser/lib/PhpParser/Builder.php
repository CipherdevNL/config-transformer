<?php

declare (strict_types=1);
namespace ConfigTransformer202109063\PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : \ConfigTransformer202109063\PhpParser\Node;
}

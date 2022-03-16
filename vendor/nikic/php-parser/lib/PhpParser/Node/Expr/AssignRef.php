<?php

declare (strict_types=1);
namespace ConfigTransformer2022031610\PhpParser\Node\Expr;

use ConfigTransformer2022031610\PhpParser\Node\Expr;
class AssignRef extends \ConfigTransformer2022031610\PhpParser\Node\Expr
{
    /** @var Expr Variable reference is assigned to */
    public $var;
    /** @var Expr Variable which is referenced */
    public $expr;
    /**
     * Constructs an assignment node.
     *
     * @param Expr  $var        Variable
     * @param Expr  $expr       Expression
     * @param array $attributes Additional attributes
     */
    public function __construct(\ConfigTransformer2022031610\PhpParser\Node\Expr $var, \ConfigTransformer2022031610\PhpParser\Node\Expr $expr, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array
    {
        return ['var', 'expr'];
    }
    public function getType() : string
    {
        return 'Expr_AssignRef';
    }
}

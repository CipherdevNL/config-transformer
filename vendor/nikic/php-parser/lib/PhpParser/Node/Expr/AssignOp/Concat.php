<?php

declare (strict_types=1);
namespace ConfigTransformer20210606\PhpParser\Node\Expr\AssignOp;

use ConfigTransformer20210606\PhpParser\Node\Expr\AssignOp;
class Concat extends \ConfigTransformer20210606\PhpParser\Node\Expr\AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_Concat';
    }
}

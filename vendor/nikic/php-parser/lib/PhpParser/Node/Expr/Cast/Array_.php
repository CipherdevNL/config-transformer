<?php

declare (strict_types=1);
namespace ConfigTransformer202108048\PhpParser\Node\Expr\Cast;

use ConfigTransformer202108048\PhpParser\Node\Expr\Cast;
class Array_ extends \ConfigTransformer202108048\PhpParser\Node\Expr\Cast
{
    public function getType() : string
    {
        return 'Expr_Cast_Array';
    }
}

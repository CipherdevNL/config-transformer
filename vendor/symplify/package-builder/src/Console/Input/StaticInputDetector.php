<?php

declare (strict_types=1);
namespace ConfigTransformer2022012610\Symplify\PackageBuilder\Console\Input;

use ConfigTransformer2022012610\Symfony\Component\Console\Input\ArgvInput;
/**
 * @api
 */
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new \ConfigTransformer2022012610\Symfony\Component\Console\Input\ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}

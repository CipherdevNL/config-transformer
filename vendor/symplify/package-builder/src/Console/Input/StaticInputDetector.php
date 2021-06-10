<?php

declare (strict_types=1);
namespace ConfigTransformer20210610\Symplify\PackageBuilder\Console\Input;

use ConfigTransformer20210610\Symfony\Component\Console\Input\ArgvInput;
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new \ConfigTransformer20210610\Symfony\Component\Console\Input\ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}

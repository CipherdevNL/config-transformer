<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer2021120410\Symfony\Component\DependencyInjection\Dumper;

/**
 * DumperInterface is the interface implemented by service container dumper classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface DumperInterface
{
    /**
     * Dumps the service container.
     * @return mixed[]|string
     * @param mixed[] $options
     */
    public function dump($options = []);
}

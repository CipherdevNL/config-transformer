<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202108294\Symfony\Component\Cache\Adapter;

use ConfigTransformer202108294\Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use ConfigTransformer202108294\Symfony\Component\Cache\Marshaller\MarshallerInterface;
use ConfigTransformer202108294\Symfony\Component\Cache\PruneableInterface;
use ConfigTransformer202108294\Symfony\Component\Cache\Traits\FilesystemTrait;
class FilesystemAdapter extends \ConfigTransformer202108294\Symfony\Component\Cache\Adapter\AbstractAdapter implements \ConfigTransformer202108294\Symfony\Component\Cache\PruneableInterface
{
    use FilesystemTrait;
    public function __construct(string $namespace = '', int $defaultLifetime = 0, string $directory = null, \ConfigTransformer202108294\Symfony\Component\Cache\Marshaller\MarshallerInterface $marshaller = null)
    {
        $this->marshaller = $marshaller ?? new \ConfigTransformer202108294\Symfony\Component\Cache\Marshaller\DefaultMarshaller();
        parent::__construct('', $defaultLifetime);
        $this->init($namespace, $directory);
    }
}

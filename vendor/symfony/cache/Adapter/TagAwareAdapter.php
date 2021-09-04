<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202109048\Symfony\Component\Cache\Adapter;

use ConfigTransformer202109048\Psr\Cache\CacheItemInterface;
use ConfigTransformer202109048\Psr\Cache\InvalidArgumentException;
use ConfigTransformer202109048\Psr\Log\LoggerAwareInterface;
use ConfigTransformer202109048\Psr\Log\LoggerAwareTrait;
use ConfigTransformer202109048\Symfony\Component\Cache\CacheItem;
use ConfigTransformer202109048\Symfony\Component\Cache\PruneableInterface;
use ConfigTransformer202109048\Symfony\Component\Cache\ResettableInterface;
use ConfigTransformer202109048\Symfony\Component\Cache\Traits\ContractsTrait;
use ConfigTransformer202109048\Symfony\Component\Cache\Traits\ProxyTrait;
use ConfigTransformer202109048\Symfony\Contracts\Cache\TagAwareCacheInterface;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class TagAwareAdapter implements \ConfigTransformer202109048\Symfony\Component\Cache\Adapter\TagAwareAdapterInterface, \ConfigTransformer202109048\Symfony\Contracts\Cache\TagAwareCacheInterface, \ConfigTransformer202109048\Symfony\Component\Cache\PruneableInterface, \ConfigTransformer202109048\Symfony\Component\Cache\ResettableInterface, \ConfigTransformer202109048\Psr\Log\LoggerAwareInterface
{
    use ContractsTrait;
    use LoggerAwareTrait;
    use ProxyTrait;
    public const TAGS_PREFIX = "\0tags\0";
    private $deferred = [];
    private $tags;
    private $knownTagVersions = [];
    private $knownTagVersionsTtl;
    private static $createCacheItem;
    private static $setCacheItemTags;
    private static $getTagsByKey;
    private static $invalidateTags;
    public function __construct(\ConfigTransformer202109048\Symfony\Component\Cache\Adapter\AdapterInterface $itemsPool, \ConfigTransformer202109048\Symfony\Component\Cache\Adapter\AdapterInterface $tagsPool = null, float $knownTagVersionsTtl = 0.15)
    {
        $this->pool = $itemsPool;
        $this->tags = $tagsPool ?: $itemsPool;
        $this->knownTagVersionsTtl = $knownTagVersionsTtl;
        self::$createCacheItem ?? (self::$createCacheItem = \Closure::bind(static function ($key, $value, \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem $protoItem) {
            $item = new \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            $item->value = $value;
            $item->expiry = $protoItem->expiry;
            $item->poolHash = $protoItem->poolHash;
            return $item;
        }, null, \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::class));
        self::$setCacheItemTags ?? (self::$setCacheItemTags = \Closure::bind(static function (\ConfigTransformer202109048\Symfony\Component\Cache\CacheItem $item, $key, array &$itemTags) {
            $item->isTaggable = \true;
            if (!$item->isHit) {
                return $item;
            }
            if (isset($itemTags[$key])) {
                foreach ($itemTags[$key] as $tag => $version) {
                    $item->metadata[\ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::METADATA_TAGS][$tag] = $tag;
                }
                unset($itemTags[$key]);
            } else {
                $item->value = null;
                $item->isHit = \false;
            }
            return $item;
        }, null, \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::class));
        self::$getTagsByKey ?? (self::$getTagsByKey = \Closure::bind(static function ($deferred) {
            $tagsByKey = [];
            foreach ($deferred as $key => $item) {
                $tagsByKey[$key] = $item->newMetadata[\ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::METADATA_TAGS] ?? [];
                $item->metadata = $item->newMetadata;
            }
            return $tagsByKey;
        }, null, \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::class));
        self::$invalidateTags ?? (self::$invalidateTags = \Closure::bind(static function (\ConfigTransformer202109048\Symfony\Component\Cache\Adapter\AdapterInterface $tagsAdapter, array $tags) {
            foreach ($tags as $v) {
                $v->expiry = 0;
                $tagsAdapter->saveDeferred($v);
            }
            return $tagsAdapter->commit();
        }, null, \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::class));
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $tags
     */
    public function invalidateTags($tags)
    {
        $ok = \true;
        $tagsByKey = [];
        $invalidatedTags = [];
        foreach ($tags as $tag) {
            \assert('' !== \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem::validateKey($tag));
            $invalidatedTags[$tag] = 0;
        }
        if ($this->deferred) {
            $items = $this->deferred;
            foreach ($items as $key => $item) {
                if (!$this->pool->saveDeferred($item)) {
                    unset($this->deferred[$key]);
                    $ok = \false;
                }
            }
            $tagsByKey = (self::$getTagsByKey)($items);
            $this->deferred = [];
        }
        $tagVersions = $this->getTagVersions($tagsByKey, $invalidatedTags);
        $f = self::$createCacheItem;
        foreach ($tagsByKey as $key => $tags) {
            $this->pool->saveDeferred($f(static::TAGS_PREFIX . $key, \array_intersect_key($tagVersions, $tags), $items[$key]));
        }
        $ok = $this->pool->commit() && $ok;
        if ($invalidatedTags) {
            $ok = (self::$invalidateTags)($this->tags, $invalidatedTags) && $ok;
        }
        return $ok;
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function hasItem($key)
    {
        if ($this->deferred) {
            $this->commit();
        }
        if (!$this->pool->hasItem($key)) {
            return \false;
        }
        $itemTags = $this->pool->getItem(static::TAGS_PREFIX . $key);
        if (!$itemTags->isHit()) {
            return \false;
        }
        if (!($itemTags = $itemTags->get())) {
            return \true;
        }
        foreach ($this->getTagVersions([$itemTags]) as $tag => $version) {
            if ($itemTags[$tag] !== $version && 1 !== $itemTags[$tag] - $version) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        foreach ($this->getItems([$key]) as $item) {
            return $item;
        }
        return null;
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $keys
     */
    public function getItems($keys = [])
    {
        if ($this->deferred) {
            $this->commit();
        }
        $tagKeys = [];
        foreach ($keys as $key) {
            if ('' !== $key && \is_string($key)) {
                $key = static::TAGS_PREFIX . $key;
                $tagKeys[$key] = $key;
            }
        }
        try {
            $items = $this->pool->getItems($tagKeys + $keys);
        } catch (\ConfigTransformer202109048\Psr\Cache\InvalidArgumentException $e) {
            $this->pool->getItems($keys);
            // Should throw an exception
            throw $e;
        }
        return $this->generateItems($items, $tagKeys);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param string $prefix
     */
    public function clear($prefix = '')
    {
        if ('' !== $prefix) {
            foreach ($this->deferred as $key => $item) {
                if (\strncmp($key, $prefix, \strlen($prefix)) === 0) {
                    unset($this->deferred[$key]);
                }
            }
        } else {
            $this->deferred = [];
        }
        if ($this->pool instanceof \ConfigTransformer202109048\Symfony\Component\Cache\Adapter\AdapterInterface) {
            return $this->pool->clear($prefix);
        }
        return $this->pool->clear();
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function deleteItem($key)
    {
        return $this->deleteItems([$key]);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param mixed[] $keys
     */
    public function deleteItems($keys)
    {
        foreach ($keys as $key) {
            if ('' !== $key && \is_string($key)) {
                $keys[] = static::TAGS_PREFIX . $key;
            }
        }
        return $this->pool->deleteItems($keys);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function save($item)
    {
        if (!$item instanceof \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem) {
            return \false;
        }
        $this->deferred[$item->getKey()] = $item;
        return $this->commit();
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function saveDeferred($item)
    {
        if (!$item instanceof \ConfigTransformer202109048\Symfony\Component\Cache\CacheItem) {
            return \false;
        }
        $this->deferred[$item->getKey()] = $item;
        return \true;
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function commit()
    {
        return $this->invalidateTags([]);
    }
    /**
     * @return array
     */
    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
    public function __destruct()
    {
        $this->commit();
    }
    private function generateItems(iterable $items, array $tagKeys) : \Generator
    {
        $bufferedItems = $itemTags = [];
        $f = self::$setCacheItemTags;
        foreach ($items as $key => $item) {
            if (!$tagKeys) {
                (yield $key => $f($item, static::TAGS_PREFIX . $key, $itemTags));
                continue;
            }
            if (!isset($tagKeys[$key])) {
                $bufferedItems[$key] = $item;
                continue;
            }
            unset($tagKeys[$key]);
            if ($item->isHit()) {
                $itemTags[$key] = $item->get() ?: [];
            }
            if (!$tagKeys) {
                $tagVersions = $this->getTagVersions($itemTags);
                foreach ($itemTags as $key => $tags) {
                    foreach ($tags as $tag => $version) {
                        if ($tagVersions[$tag] !== $version && 1 !== $version - $tagVersions[$tag]) {
                            unset($itemTags[$key]);
                            continue 2;
                        }
                    }
                }
                $tagVersions = $tagKeys = null;
                foreach ($bufferedItems as $key => $item) {
                    (yield $key => $f($item, static::TAGS_PREFIX . $key, $itemTags));
                }
                $bufferedItems = null;
            }
        }
    }
    private function getTagVersions(array $tagsByKey, array &$invalidatedTags = [])
    {
        $tagVersions = $invalidatedTags;
        foreach ($tagsByKey as $tags) {
            $tagVersions += $tags;
        }
        if (!$tagVersions) {
            return [];
        }
        if (!($fetchTagVersions = 1 !== \func_num_args())) {
            foreach ($tagsByKey as $tags) {
                foreach ($tags as $tag => $version) {
                    if ($tagVersions[$tag] > $version) {
                        $tagVersions[$tag] = $version;
                    }
                }
            }
        }
        $now = \microtime(\true);
        $tags = [];
        foreach ($tagVersions as $tag => $version) {
            $tags[$tag . static::TAGS_PREFIX] = $tag;
            if ($fetchTagVersions || !isset($this->knownTagVersions[$tag])) {
                $fetchTagVersions = \true;
                continue;
            }
            $version -= $this->knownTagVersions[$tag][1];
            if (0 !== $version && 1 !== $version || $now - $this->knownTagVersions[$tag][0] >= $this->knownTagVersionsTtl) {
                // reuse previously fetched tag versions up to the ttl, unless we are storing items or a potential miss arises
                $fetchTagVersions = \true;
            } else {
                $this->knownTagVersions[$tag][1] += $version;
            }
        }
        if (!$fetchTagVersions) {
            return $tagVersions;
        }
        foreach ($this->tags->getItems(\array_keys($tags)) as $tag => $version) {
            $tagVersions[$tag = $tags[$tag]] = $version->get() ?: 0;
            if (isset($invalidatedTags[$tag])) {
                $invalidatedTags[$tag] = $version->set(++$tagVersions[$tag]);
            }
            $this->knownTagVersions[$tag] = [$now, $tagVersions[$tag]];
        }
        return $tagVersions;
    }
}

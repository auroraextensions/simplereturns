<?php
/**
 * Label.php
 *
 * Return labels adapter model.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
namespace AuroraExtensions\Returns\Model;

use AuroraExtensions\{
    Cache\Model\Type as LabelCache,
    Returns\Shared\DictionaryInterface
};

use Magento\{
    Framework\App\Cache\StateInterface,
    Framework\Serialize\Serializer\Json,
    Sales\Api\Data\OrderInterface
};

class Label implements DictionaryInterface
{
    /** @property LabelCache $cache */
    protected $cache;

    /** @property StateInterface $cacheState */
    protected $cacheState;

    /** @property string|null $image */
    protected $image;

    /** @property Json $serializer */
    protected $serializer;

    /**
     * @param LabelCache $cache
     * @param StateInterface $cacheState
     * @param Json $serializer
     * @return void
     */
    public function __construct(
        LabelCache $cache,
        StateInterface $cacheState,
        Json $serializer
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->serializer = $serializer;
    }

    /**
     * Check if bedrock cache is enabled.
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(LabelCache::TYPE_IDENTIFIER);
    }

    /**
     * Get cache instance.
     *
     * @return LabelCache
     */
    protected function getCache()
    {
        return $this->cache;
    }

    /**
     * Get label image.
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get cache key from order data.
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getCacheKey(OrderInterface $order): string
    {
        /** @var array $parts */
        $parts = [
            self::LABEL_CACHE_ID,
            $order->getStoreId(),
            $order->getIncrementId(),
            $order->getProtectCode(),
        ];

        return implode('_', $parts);
    }

    /**
     * Get label image from cache storage.
     *
     * @return mixed
     */
    public function getCachedImage(string $cacheKey)
    {
        if ($cache = $this->getCache()->load($cacheKey)) {
            return $this->serializer->unserialize($cache);
        }

        return null;
    }

    /**
     * Check if there is cached return label for order.
     *
     * @param string $cacheKey
     * @return bool
     */
    public function hasCachedImage(string $cacheKey): bool
    {
        if ($this->getCachedImage($cacheKey) !== null) {
            return true;
        }

        return false;
    }

    /**
     * Set label image in cache.
     *
     * @param string $cacheKey
     * @param mixed $image
     * @return $this
     */
    public function setCachedImage(string $cacheKey, $image)
    {
        /* Serialize data and save to cache. */
        $this->getCache()->save(
            $this->serializer->serialize($image),
            $cacheKey,
            [
                LabelCache::CACHE_TAG,
            ]
        );

        return $this;
    }

    /**
     * Set label image.
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
}

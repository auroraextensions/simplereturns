<?php
/**
 * Methods.php
 *
 * Shipping method options source model.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ServiceModel\System\Source;

use Magento\Framework\Option\ArrayInterface;

class Methods implements ArrayInterface
{
    /** @property array $options */
    protected static $options = [];

    /** @property array $values */
    protected static $values = [
        'UPS Ground' => '03',
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        array_walk(self::$values, [$this, 'setOption']);
    }

    /**
     * Set option key/value array on self::$options.
     *
     * @param string $value
     * @param string $key
     * @return void
     */
    protected function setOption($value, $key)
    {
        self::$options[] = [
            'label' => __($key),
            'value' => $value,
        ];
    }
    /**
     * Get formatted option key/value pairs.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return self::$options;
    }
}

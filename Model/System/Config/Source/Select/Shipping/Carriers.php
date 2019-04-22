<?php
/**
 * Carriers.php
 *
 * Shipping carrier options source model.
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
namespace AuroraExtensions\Returns\Model\System\Config\Source\Select\Shipping;

use Magento\Framework\Option\ArrayInterface;

class Carriers implements ArrayInterface
{
    /** @property array $options */
    protected static $options = [];

    /** @property array $values */
    protected static $values = [
        'UPS' => 'ups',
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

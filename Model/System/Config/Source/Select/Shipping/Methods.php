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
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
namespace AuroraExtensions\Returns\Model\System\Config\Source\Select\Shipping;

use Magento\Framework\Option\ArrayInterface;

class Methods implements ArrayInterface
{
    /** @property array $options */
    protected static $options = [];

    /** @property array $methods */
    protected static $methods = [
        'UPS Ground' => '03',
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        array_walk(self::$methods, [$this, 'setOption']);
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

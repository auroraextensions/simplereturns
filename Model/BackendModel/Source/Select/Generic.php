<?php
/**
 * Generic.php
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

namespace AuroraExtensions\SimpleReturns\Model\BackendModel\Source\Select;

use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use Magento\Framework\Option\ArrayInterface;

class Generic implements ArrayInterface
{
    /** @property array $options */
    protected $options = [];

    /**
     * @param ModuleConfig $moduleConfig
     * @param string $key
     * @return void
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        string $key
    ) {
        /** @var array $data */
        $data = array_flip(
            $moduleConfig->getSettings()->getData($key) ?? []
        );

        array_walk(
            $data,
            [
                $this,
                'setOption'
            ]
        );
    }

    /**
     * @param int|string|null $value
     * @param int|string $key
     * @return void
     */
    protected function setOption($value, $key): void
    {
        $this->options[] = [
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
        return $this->options;
    }
}

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

namespace AuroraExtensions\SimpleReturns\Model\BackendModel\System\Source;

use Magento\Framework\{
    DataObject,
    DataObject\Factory as DataObjectFactory,
    Option\ArrayInterface
};

class Methods implements ArrayInterface
{
    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property array $options */
    protected $options = [];

    /** @property DataObject $settings */
    protected $settings;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->settings = $this->dataObjectFactory->create($data);

        /** @var array $methods */
        $methods = array_flip(
            $this->settings->getData('methods') ?? []
        );

        array_walk(
            $methods,
            [
                $this,
                'setOption'
            ]
        );
    }

    /**
     * Create/update option key/value array.
     *
     * @param string $value
     * @param string $key
     * @return void
     */
    protected function setOption($value, $key)
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

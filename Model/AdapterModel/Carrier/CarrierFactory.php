<?php
/**
 * CarrierFactory.php
 *
 * Factory for creating shipping carrier models, which
 * requires the ObjectManager for instance generation.
 *
 * @link https://devdocs.magento.com/guides/v2.3/extension-dev-guide/factories.html
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

namespace AuroraExtensions\SimpleReturns\Model\AdapterModel\Carrier;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Exception\InvalidCarrierException,
    Helper\Config as ConfigHelper,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    DataObject,
    DataObject\Factory as DataObjectFactory,
    ObjectManagerInterface
};
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Ups\Model\Carrier as UpsModel;

class CarrierFactory implements ModuleComponentInterface
{
    /** @property array $carriers */
    protected $carriers = [
        UpsModel::CODE => UpsModel::class,
    ];

    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property ObjectManagerInterface $objectManager */
    protected $objectManager;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param ExceptionFactory $exceptionFactory
     * @param ModuleConfig $moduleConfig
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ExceptionFactory $exceptionFactory,
        ModuleConfig $moduleConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->moduleConfig = $moduleConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * Create carrier model via ObjectManager.
     *
     * @param string $code The carrier code.
     * @return CarrierInterface
     * @throws Exception
     */
    public function create(string $code): CarrierInterface
    {
        /** @var array $codes */
        $codes = array_keys($this->carriers);

        if (!in_array($code, $codes)) {
            /** @var InvalidCarrierException $exception */
            $exception = $this->exceptionFactory->create(
                InvalidCarrierException::class,
                __(self::ERROR_INVALID_CARRIER_CODE)
            );

            throw $exception;
        }

        return $this->objectManager->create($this->carriers[$code]);
    }
}

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
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\AdapterModel\Shipping\Carrier
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\AdapterModel\Shipping\Carrier;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Component\System\ModuleConfigTrait,
    Exception\InvalidCarrierException,
    Shared\ModuleComponentInterface,
    Csi\System\Module\ConfigInterface
};
use Magento\Dhl\Model\Carrier as DHL;
use Magento\Fedex\Model\Carrier as Fedex;
use Magento\Framework\{
    DataObject,
    DataObject\Factory as DataObjectFactory,
    ObjectManagerInterface
};
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Ups\Model\Carrier as UPS;
use Magento\Usps\Model\Carrier as USPS;

use function __;
use function array_keys;
use function in_array;

class CarrierFactory implements ModuleComponentInterface
{
    use ModuleConfigTrait;

    /** @var array $carriers */
    protected $carriers = [
        DHL::CODE   => DHL::class,
        Fedex::CODE => Fedex::class,
        UPS::CODE   => UPS::class,
        USPS::CODE  => USPS::class,
    ];

    /** @var DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var ObjectManagerInterface $objectManager */
    protected $objectManager;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param ExceptionFactory $exceptionFactory
     * @param ConfigInterface $moduleConfig
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ExceptionFactory $exceptionFactory,
        ConfigInterface $moduleConfig,
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
     * @throws InvalidCarrierException
     */
    public function create(string $code): CarrierInterface
    {
        /** @var array $codes */
        $codes = array_keys($this->getConfig()->getCarriers());

        if (!in_array($code, $codes)) {
            /** @var InvalidCarrierException $exception */
            $exception = $this->exceptionFactory->create(
                InvalidCarrierException::class,
                __(
                    self::ERROR_INVALID_CARRIER_CODE,
                    $code
                )
            );
            throw $exception;
        }

        return $this->objectManager->create($this->carriers[$code]);
    }
}

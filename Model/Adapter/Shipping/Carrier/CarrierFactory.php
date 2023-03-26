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
 * @package     AuroraExtensions\SimpleReturns\Model\Adapter\Shipping\Carrier
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Adapter\Shipping\Carrier;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Exception\InvalidCarrierException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;

use function __;

class CarrierFactory
{
    /** @var string[] $carriers */
    private $carriers;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var ObjectManagerInterface $objectManager */
    private $objectManager;

    /**
     * @param ExceptionFactory $exceptionFactory
     * @param ObjectManagerInterface $objectManager
     * @param string[] $carriers
     * @return void
     */
    public function __construct(
        ExceptionFactory $exceptionFactory,
        ObjectManagerInterface $objectManager,
        array $carriers = []
    ) {
        $this->exceptionFactory = $exceptionFactory;
        $this->objectManager = $objectManager;
        $this->carriers = $carriers;
    }

    /**
     * @param string $code The carrier code.
     * @return CarrierInterface
     * @throws InvalidCarrierException
     */
    public function create(string $code): CarrierInterface
    {
        if (!isset($this->carriers[$code])) {
            /** @var InvalidCarrierException $exception */
            $exception = $this->exceptionFactory->create(
                InvalidCarrierException::class,
                __(
                    '"%1" is not a valid carrier code.',
                    $code
                )
            );
            throw $exception;
        }

        return $this->objectManager->create($this->carriers[$code]);
    }
}

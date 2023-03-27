<?php
/**
 * EligibilityValidator.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Validator\Sales\Order
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Validator\Sales\Order;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Model\System\Module\Config as ModuleConfig;
use DateTime;
use DateTimeFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;

class EligibilityValidator
{
    /** @var PriceCurrencyInterface $currency */
    private $currency;

    /** @var DateTimeFactory $dateTimeFactory */
    private $dateTimeFactory;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var ModuleConfig $moduleConfig */
    private $moduleConfig;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /**
     * @param PriceCurrencyInterface $currency
     * @param DateTimeFactory $dateTimeFactory
     * @param ExceptionFactory $exceptionFactory
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param ProductRepositoryInterface $productRepository
     * @return void
     */
    public function __construct(
        PriceCurrencyInterface $currency,
        DateTimeFactory $dateTimeFactory,
        ExceptionFactory $exceptionFactory,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        ProductRepositoryInterface $productRepository
    ) {
        $this->currency = $currency;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->productRepository = $productRepository;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        /** @var bool $isEnabled */
        $isEnabled = $this->isSimpleReturnEnabledForItems($order);

        /** @var bool $isAgeBelow */
        $isAgeBelow = $this->isOrderAgeBelowThreshold($order);

        /** @var bool $isAmountAbove */
        $isAmountAbove = $this->isOrderSubtotalAboveMinimum($order);
        return ($isEnabled && $isAgeBelow && $isAmountAbove);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderAgeBelowThreshold(OrderInterface $order): bool
    {
        /** @var int $ageLimit */
        $ageLimit = $this->moduleConfig->getOrderAgeMaximum($order->getStoreId());

        /** @var DateTime $createdDateTime */
        $createdDateTime = $this->dateTimeFactory->create([
            'time' => $order->getCreatedAt()
        ]);

        /** @var DateTime $currentDateTime */
        $currentDateTime = $this->dateTimeFactory->create();
        return ($ageLimit > 0 && $createdDateTime->diff($currentDateTime)->days <= $ageLimit);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderSubtotalAboveMinimum(OrderInterface $order): bool
    {
        /** @var float $minimumAmount */
        $minimumAmount = $this->moduleConfig
            ->getOrderAmountMinimum($order->getStoreId());

        /** @var float $orderSubtotal */
        $orderSubtotal = (float) $order->getSubtotal();
        return ($minimumAmount > 0 && $minimumAmount <= $orderSubtotal);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isSimpleReturnEnabledForItems(OrderInterface $order): bool
    {
        /** @var array $items */
        $items = $order->getAllItems();

        /** @var Item $item */
        foreach ($items as $item) {
            /** @var ProductInterface $product */
            $product = $this->productRepository->getById($item->getProductId());

            /** @var bool $allowed */
            $allowed = (bool) $product->getSimpleReturn();

            if (!$allowed) {
                return false;
            }
        }

        return true;
    }
}

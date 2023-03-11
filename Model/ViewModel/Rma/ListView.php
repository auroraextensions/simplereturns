<?php
/** 
 * ListView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */ 
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Helper\Config as ConfigHelper,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ValidatorModel\Sales\Order\EligibilityValidator,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\Api\Data\OrderInterface;

use function count;

class ListView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @var ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var EligibilityValidator $validator */
    protected $validator;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ModuleConfig $moduleConfig
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param EligibilityValidator $validator
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ModuleConfig $moduleConfig,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        EligibilityValidator $validator,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->moduleConfig = $moduleConfig;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->validator = $validator;
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(
        string $type,
        string $key
    ): string {
        /** @var array $labels */
        $labels = $this->moduleConfig->getSettings()->getData($type);
        return $labels[$key] ?? $key;
    }

    /**
     * @param OrderInterface $order
     * @return SimpleReturnInterface|null
     */
    public function getSimpleReturn(OrderInterface $order): ?SimpleReturnInterface
    {
        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->get($order);

            if ($rma->getId()) {
                return $rma;
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            /* No action required. */
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function hasSimpleReturn(OrderInterface $order): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn($order);
        return $rma !== null ? (bool) $rma->getId() : false;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaCreateUrl(OrderInterface $order): string
    {
        return $this->urlBuilder->getUrl(
            'simplereturns/rma/create',
            [
                'order_id' => $order->getRealOrderId(),
                'code'     => $order->getProtectCode(),
                '_secure'  => true,
            ]
        );
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaViewUrl(OrderInterface $order): string
    {
        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn($order);

        return $this->urlBuilder->getUrl(
            'simplereturns/rma/view',
            [
                'rma_id'  => $rma->getId(),
                'token'   => $rma->getToken(),
                '_secure' => true,
            ]
        );
    }

    /**
     * @return bool
     */
    public function hasOrders(): bool
    {
        /** @var array $orders */
        $orders = $this->getData('orders') ?? [];
        return count($orders) > 0;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        return $this->validator->isOrderEligible($order);
    }
}

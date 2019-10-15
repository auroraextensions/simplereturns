<?php
/**
 * HistoryView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Sales\Order;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
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

class HistoryView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property EligibilityValidator $validator */
    protected $validator;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param EligibilityValidator $validator
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        EligibilityValidator $validator
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->validator = $validator;
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
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (LocalizedException $e) {
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

        if ($rma !== null) {
            return true;
        }

        return false;
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
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        return $this->validator->isOrderEligible($order);
    }
}

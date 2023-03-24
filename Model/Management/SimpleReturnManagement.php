<?php
/**
 * SimpleReturnManagement.php
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

namespace AuroraExtensions\SimpleReturns\Model\ManagementModel;

use AuroraExtensions\SimpleReturns\{
    Api\SimpleReturnManagementInterface,
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    HTTP\PhpEnvironment\RemoteAddress
};
use Magento\Sales\Api\OrderRepositoryInterface;

class SimpleReturnManagement implements SimpleReturnManagementInterface, ModuleComponentInterface
{
    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param RemoteAddress $remoteAddress
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RemoteAddress $remoteAddress,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * Add RMA comment to order.
     *
     * @param SimpleReturnInterface $rma
     * @param string $comment
     * @return bool
     */
    public function addOrderComment(
        SimpleReturnInterface $rma,
        string $comment
    ): bool
    {
        /** @var int|string|null $orderId */
        $orderId = $rma->getOrderId();
        $orderId = $orderId !== null && is_numeric($orderId)
            ? (int) $orderId
            : null;

        if ($orderId !== null) {
            try {
                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($orderId);

                if ($order->getId()) {
                    /* Insert comment and update order. */
                    $order->addStatusHistoryComment($comment);
                    $this->orderRepository->save($order);

                    return true;
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return false;
    }
}

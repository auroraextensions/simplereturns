<?php
/**
 * SimpleReturnManagement.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Management
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Management;

use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnManagementInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

use function is_numeric;

class SimpleReturnManagement implements SimpleReturnManagementInterface
{
    /** @var LoggerInterface $logger */
    private $logger;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param RemoteAddress $remoteAddress
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RemoteAddress $remoteAddress,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->logger = $logger;
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
    ): bool {
        /** @var int|string|null $orderId */
        $orderId = $rma->getOrderId();
        $orderId = is_numeric($orderId) ? (int) $orderId : null;

        if ($orderId === null) {
            return false;
        }

        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($orderId);

            if (!$order->getId()) {
                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class
                );
                throw $exception;
            }

            /* Insert comment and update order. */
            $order->addStatusHistoryComment($comment);
            $this->orderRepository->save($order);
            return true;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}

<?php
/**
 * IncrementId.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column\Order
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column\Order;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Throwable;

class IncrementId extends Column
{
    public const COLUMN_KEY = 'increment_id';
    public const ENTITY_KEY = 'order_id';

    /** @var OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            /** @var array $item */
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var int|string|null $orderId */
                $orderId = $item[static::ENTITY_KEY] ?? null;

                if ($orderId !== null) {
                    $item[static::COLUMN_KEY] = $this->getIncrementId((int) $orderId);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $orderId
     * @return string|null
     */
    private function getIncrementId(int $orderId): ?string
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($orderId);
            return (string) $order->getRealOrderId();
        } catch (Throwable $e) {
            return null;
        }
    }
}

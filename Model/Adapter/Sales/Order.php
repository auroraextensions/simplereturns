<?php
/**
 * Order.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Adapter\Sales
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Adapter\Sales;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

use function __;
use function array_values;
use function substr;

class Order
{
    private const FIELD_CUSTOMER_ID = 'customer_id';
    private const FIELD_INCREMENT_ID = 'increment_id';
    private const FIELD_PROTECT_CODE = 'protect_code';
    private const ZIP_CODE_INDEX = 0;
    private const ZIP_CODE_LENGTH = 5;

    /** @var CustomerRepositoryInterface $customerRepository */
    private $customerRepository;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FilterBuilder $filterBuilder */
    private $filterBuilder;

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param ExceptionFactory $exceptionFactory
     * @param FilterBuilder $filterBuilder
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return void
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ExceptionFactory $exceptionFactory,
        FilterBuilder $filterBuilder,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->exceptionFactory = $exceptionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get orders collection by customer email.
     *
     * @param string $email
     * @param string $zipCode
     * @return OrderInterface[]
     */
    public function getOrdersByCustomerEmailAndZipCode(
        string $email,
        string $zipCode
    ): array {
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->get($email);

            if ($customer && $customer->getId()) {
                /** @var array $filters */
                $filters = [
                    $this->filterBuilder
                        ->setField(self::FIELD_CUSTOMER_ID)
                        ->setValue($customer->getId())
                        ->create()
                ];
                return $this->getOrdersByFilters($filters);
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(
                __(
                    'Could not find any orders associated with email: %1',
                    $email
                )
            );
        }

        return [];
    }

    /**
     * Get orders collection by order increment ID and zip code.
     *
     * @param string $incrementId
     * @param string $zipCode
     * @return OrderInterface[]
     */
    public function getOrdersByIncrementIdAndZipCode(
        string $incrementId,
        string $zipCode
    ): array {
        /** @var array $orders */
        $orders = [];

        try {
            /** @var array $filters */
            $filters = [
                $this->filterBuilder
                     ->setField(self::FIELD_INCREMENT_ID)
                     ->setValue($incrementId)
                     ->create(),
            ];

            /** @var OrderInterface[] $orders */
            $orders = $this->getOrdersByFilters($filters);

            if (empty($orders)) {
                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __(
                        'Could not find an order #%1 with billing or shipping zip code: %2',
                        $incrementId,
                        $zipCode
                    )
                );
                throw $exception;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $orders;
    }

    /**
     * Get orders collection by order increment ID and protect code.
     *
     * @param string $incrementId
     * @param string $protectCode
     * @return OrderInterface[]
     */
    public function getOrdersByIncrementIdAndProtectCode(
        string $incrementId,
        string $protectCode
    ): array {
        /** @var array $data */
        $data = [];

        try {
            /** @var array $filters */
            $filters = [
                $this->filterBuilder
                     ->setField(self::FIELD_INCREMENT_ID)
                     ->setValue($incrementId)
                     ->create(),
                $this->filterBuilder
                     ->setField(self::FIELD_PROTECT_CODE)
                     ->setValue($protectCode)
                     ->create(),
            ];

            /** @var OrderInterface[] $orders */
            $orders = $this->getOrdersByFilters($filters);

            foreach ($orders as $order) {
                if ($order->getProtectCode() === $protectCode) {
                    $data[] = $order;
                }
            }

            if (empty($data)) {
                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __('The requested return label URL was invalid. Please verify and try again.')
                );
                throw $exception;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $data;
    }

    /**
     * Get orders by field key/value pairs.
     *
     * @param array $fields
     * @return array
     */
    public function getOrdersByFields(array $fields = []): array
    {
        /** @var array $filters */
        $filters = [];

        /** @var string $field */
        /** @var mixed $value */
        foreach ($fields as $field => $value) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setValue($value)
                ->create();
        }

        try {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate any matching orders.')
            );

            /** @var OrderInterface[] $orders */
            $orders = array_values(
                $this->getOrdersByFilters($filters)
            );

            if (!empty($orders)) {
                return $orders;
            }

            throw $exception;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return [];
    }

    /**
     * Get orders collection by filters.
     *
     * @param array $filters
     * @return array
     */
    public function getOrdersByFilters(array $filters = []): array
    {
        /** @var SearchCriteriaInterface $criteria */
        $criteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->create();
        return $this->orderRepository
            ->getList($criteria)
            ->getItems();
    }

    /**
     * Truncate order billing/shipping zip code.
     *
     * @param string $zipCode
     * @return string
     * @static
     */
    public static function truncateZipCode(string $zipCode): string
    {
        return substr(
            $zipCode,
            self::ZIP_CODE_INDEX,
            self::ZIP_CODE_LENGTH
        );
    }
}

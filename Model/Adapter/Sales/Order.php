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
 * @package     AuroraExtensions\SimpleReturns\Model\AdapterModel\Sales
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\AdapterModel\Sales;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\{
    Api\FilterBuilder,
    Api\SearchCriteriaBuilder,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface
};
use Magento\Sales\Api\OrderRepositoryInterface;

use function __;
use function array_values;
use function substr;

class Order implements ModuleComponentInterface
{
    /** @var CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var FilterBuilder $filterBuilder */
    protected $filterBuilder;

    /** @var MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @var OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
                    self::ERROR_NO_SUCH_ENTITY_FOUND_FOR_EMAIL,
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
                        self::ERROR_NO_SUCH_ENTITY_FOUND_FOR_ORDER_ID_ZIP_CODE,
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
                $this->filterBuilder->setField(self::FIELD_INCREMENT_ID)
                     ->setValue($incrementId)
                     ->create(),
                $this->filterBuilder->setField(self::FIELD_PROTECT_CODE)
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
                    __(self::ERROR_INVALID_RETURN_LABEL_URL)
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
        /** @var SearchCriteria $criteria */
        $criteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        return $this->orderRepository->getList($criteria)->getItems();
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

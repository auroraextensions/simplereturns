<?php
/**
 * Orders.php
 *
 * Customer orders adapter model.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
namespace AuroraExtensions\SimpleReturns\Model\Adapter;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\{
    Customer\Api\CustomerRepositoryInterface,
    Framework\Api\FilterBuilder,
    Framework\Api\SearchCriteriaBuilder,
    Framework\Exception\NoSuchEntityException,
    Framework\Message\ManagerInterface as MessageManagerInterface,
    Sales\Api\OrderRepositoryInterface
};

class Orders implements ModuleComponentInterface
{
    /** @property CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @property FilterBuilder $filterBuilder */
    protected $filterBuilder;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param FilterBuilder $filterBuilder
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return void
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        FilterBuilder $filterBuilder,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrdersByCustomerEmailAndZipCode(string $email, string $zipCode): array
    {
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->get($email);

            if ($customer && $customer->getId()) {
                /** @var array $filters */
                $filters = [
                    $this->filterBuilder->setField(self::FIELD_CUSTOMER_ID)
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
    public function getOrdersByIncrementIdAndZipCode(string $incrementId, string $zipCode): array
    {
        /** @var array $orders */
        $orders = [];

        try {
            /** @var array $filters */
            $filters = [
                $this->filterBuilder->setField(self::FIELD_INCREMENT_ID)
                ->setValue($incrementId)
                ->create()
            ];

            /** @var OrderInterface[] $orders */
            $orders = $this->getOrdersByFilters($filters);

            if (empty($orders)) {
                throw new NoSuchEntityException(
                    __(
                        self::ERROR_NO_SUCH_ENTITY_FOUND_FOR_ORDER_ID_ZIP_CODE,
                        $incrementId,
                        $zipCode
                    )
                );
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
    public function getOrdersByIncrementIdAndProtectCode(string $incrementId, string $protectCode): array
    {
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
                ->create()
            ];

            /** @var OrderInterface[] $orders */
            $orders = $this->getOrdersByFilters($filters);

            foreach ($orders as $order) {
                if ($order->getProtectCode() === $protectCode) {
                    $data[] = $order;
                }
            }

            if (empty($data)) {
                throw new NoSuchEntityException(
                    __(self::ERROR_INVALID_RETURN_LABEL_URL)
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $data;
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
     */
    public static function truncateZipCode(string $zipCode)
    {
        return substr($zipCode, self::ZIP_CODE_INDEX, self::ZIP_CODE_LENGTH);
    }
}

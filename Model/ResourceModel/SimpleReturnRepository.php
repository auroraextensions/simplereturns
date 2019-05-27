<?php
/**
 * SimpleReturnRepository.php
 *
 * RMA repository model.
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
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel;

use AuroraExtensions\SimpleReturns\{
    Api\SimpleReturnRepositoryInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\Data\SimpleReturnSearchResultsInterfaceFactory,
    Exception\ExceptionFactory,
    Model\SimpleReturn as SimpleReturnAdapter,
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\Api\SearchCriteriaInterface,
    Framework\Api\SortOrder,
    Framework\Exception\NoSuchEntityException,
    Sales\Api\Data\OrderInterface
};

class SimpleReturnRepository extends AbstractRepository implements
    SimpleReturnRepositoryInterface,
    ModuleComponentInterface
{
    /** @property SimpleReturn\CollectionFactory $collectionFactory */
    protected $collectionFactory;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property SimpleReturnSearchResultsInterfaceFactory $searchResultsFactory */
    protected $searchResultsFactory;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnResource $simpleReturnResource */
    protected $simpleReturnResource;

    /**
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnResource $simpleReturnResource
     * @param SimpleReturn\CollectionFactory $collectionFactory
     * @param SimpleReturnSearchResultsInterfaceFactory $searchResultsFactory
     * @param ExceptionFactory $exceptionFactory
     * @return void
     */
    public function __construct(
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnResource $simpleReturnResource,
        SimpleReturn\CollectionFactory $collectionFactory,
        SimpleReturnSearchResultsInterfaceFactory $searchResultsFactory,
        ExceptionFactory $exceptionFactory
    ) {
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnResource = $simpleReturnResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * Get SimpleReturn RMA for order.
     *
     * @param OrderInterface $order
     * @return SimpleReturnInterface
     */
    public function get(OrderInterface $order): SimpleReturnInterface
    {
        /** @var SimpleReturnAdapter $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load(
            $rma,
            $order->getId(),
            self::SQL_COLUMN_SIMPLERETURN_ORDER_ID_FIELD
        );

        if (!$rma->getId()) {
            throw $this->exceptionFactory->create(
                __('Unable to locate RMA information for the requested order.')
            );
        }

        return $rma;
    }

    /**
     * Get SimpleReturn RMA by ID.
     *
     * @param int $id
     * @return SimpleReturnInterface
     */
    public function getById(int $id): SimpleReturnInterface
    {
        /** @var SimpleReturnAdapter $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load($rma, $id);

        if (!$rma->getId()) {
            throw $this->exceptionFactory->create(
                __('Unable to locate RMA information for the requested order.')
            );
        }

        return $rma;
    }

    /**
     * Save SimpleReturn RMA object.
     *
     * @param SimpleReturnInterface $rma
     * @return int
     */
    public function save(SimpleReturnInterface $rma): int
    {
        $this->simpleReturnResource->save($rma);
        return $rma->getId();
    }
}

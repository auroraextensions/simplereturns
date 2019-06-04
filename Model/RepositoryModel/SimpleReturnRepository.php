<?php
/**
 * SimpleReturnRepository.php
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
    Api\AbstractCollectionInterfaceFactory,
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
    Framework\Api\SearchResultsInterface,
    Framework\Api\SearchResultsInterfaceFactory,
    Framework\Exception\NoSuchEntityException,
    Sales\Api\Data\OrderInterface
};

class SimpleReturnRepository extends AbstractRepository implements
    SimpleReturnRepositoryInterface,
    ModuleComponentInterface
{
    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnResource $simpleReturnResource */
    protected $simpleReturnResource;

    /**
     * @param AbstractCollectionInterfaceFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnResource $simpleReturnResource
     * @param ExceptionFactory $exceptionFactory
     * @return void
     */
    public function __construct(
        AbstractCollectionInterfaceFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnResource $simpleReturnResource,
        ExceptionFactory $exceptionFactory
    ) {
        parent::__construct(
            $collectionFactory,
            $searchResultsFactory
        );

        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnResource = $simpleReturnResource;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
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
            self::SQL_COLUMN_RMA_ORDER_ID_FIELD
        );

        if (!$rma->getId()) {
            throw $this->exceptionFactory->create(
                __('Unable to locate RMA information for the requested order.')
            );
        }

        return $rma;
    }

    /**
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
     * @param SimpleReturnInterface $rma
     * @return int
     */
    public function save(SimpleReturnInterface $rma): int
    {
        $this->simpleReturnResource->save($rma);
        return $rma->getId();
    }

    /**
     * @param SimpleReturnInterface $rma
     * @return bool
     */
    public function delete(SimpleReturnInterface $rma): bool
    {
        return $this->deleteById($rma->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        /** @var SimpleReturnAdapter $rma */
        $rma = $this->simpleReturnFactory->create();
        $rma->setId($id);

        return (bool) $this->simpleReturnResource->delete($rma);
    }
}

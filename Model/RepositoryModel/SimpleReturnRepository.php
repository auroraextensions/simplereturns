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

namespace AuroraExtensions\SimpleReturns\Model\RepositoryModel;

use AuroraExtensions\SimpleReturns\{
    Api\AbstractCollectionInterface,
    Api\SimpleReturnRepositoryInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Exception\ExceptionFactory,
    Model\DataModel\SimpleReturn,
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\SearchResultsInterface,
    Exception\NoSuchEntityException
};
use Magento\Sales\Api\Data\OrderInterface;

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
     * @param ExceptionFactory $exceptionFactory
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnResource $simpleReturnResource
     * @return void
     */
    public function __construct(
        $collectionFactory,
        $searchResultsFactory,
        ExceptionFactory $exceptionFactory,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnResource $simpleReturnResource
    ) {
        parent::__construct(
            $collectionFactory,
            $searchResultsFactory
        );

        $this->exceptionFactory = $exceptionFactory;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnResource = $simpleReturnResource;
    }

    /**
     * @param OrderInterface $order
     * @return SimpleReturnInterface
     * @throws NoSuchEntityException
     */
    public function get(OrderInterface $order): SimpleReturnInterface
    {
        /** @var SimpleReturn $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load(
            $rma,
            $order->getId(),
            self::SQL_COLUMN_RMA_ORDER_ID_FIELD
        );

        if (!$rma->getId()) {
            throw $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate SimpleReturn RMA information.')
            );
        }

        return $rma;
    }

    /**
     * @param int $id
     * @return SimpleReturnInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): SimpleReturnInterface
    {
        /** @var SimpleReturn $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load($rma, $id);

        if (!$rma->getId()) {
            throw $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate SimpleReturn RMA information.')
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
        /** @var SimpleReturn $rma */
        $rma = $this->simpleReturnFactory->create();
        $rma->setId($id);

        return (bool) $this->simpleReturnResource->delete($rma);
    }
}

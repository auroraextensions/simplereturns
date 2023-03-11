<?php
/**
 * SimpleReturnRepository.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\RepositoryModel
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\RepositoryModel;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\AbstractCollectionInterfaceFactory,
    Api\SimpleReturnRepositoryInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Model\DataModel\SimpleReturn,
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\SearchResultsInterface,
    Exception\NoSuchEntityException
};
use Magento\Sales\Api\Data\OrderInterface;

use function __;

class SimpleReturnRepository extends AbstractRepository implements
    SimpleReturnRepositoryInterface,
    ModuleComponentInterface
{
    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @var SimpleReturnResource $simpleReturnResource */
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
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate SimpleReturn RMA information.')
            );
            throw $exception;
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
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate SimpleReturn RMA information.')
            );
            throw $exception;
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
        return (int) $rma->getId();
    }

    /**
     * @param SimpleReturnInterface $rma
     * @return bool
     */
    public function delete(SimpleReturnInterface $rma): bool
    {
        return $this->deleteById((int) $rma->getId());
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

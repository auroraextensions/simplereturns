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

use AuroraExtensions\ModuleComponents\Api\AbstractCollectionInterfaceFactory;
use AuroraExtensions\ModuleComponents\Component\Repository\AbstractRepositoryTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnSearchResultsInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\SimpleReturn as SimpleReturnResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\SimpleReturn\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

use function __;

class SimpleReturnRepository implements SimpleReturnRepositoryInterface
{
    /**
     * @var AbstractCollectionInterfaceFactory $collectionFactory
     * @var SearchResultsInterfaceFactory $searchResultsFactory
     * @method void addFilterGroupToCollection()
     * @method string getDirection()
     * @method SearchResultsInterface getList()
     */
    use AbstractRepositoryTrait;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    private $simpleReturnFactory;

    /** @var SimpleReturnResource $simpleReturnResource */
    private $simpleReturnResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param SimpleReturnSearchResultsInterfaceFactory $searchResultsFactory
     * @param ExceptionFactory $exceptionFactory
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnResource $simpleReturnResource
     * @return void
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SimpleReturnSearchResultsInterfaceFactory $searchResultsFactory,
        ExceptionFactory $exceptionFactory,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnResource $simpleReturnResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
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
        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load(
            $rma,
            $order->getId(),
            OrderItemInterface::ORDER_ID
        );

        if (!$rma->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate RMA information.')
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
        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load($rma, $id);

        if (!$rma->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate RMA information.')
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
        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();
        $rma->setId($id);
        return (bool) $this->simpleReturnResource->delete($rma);
    }
}

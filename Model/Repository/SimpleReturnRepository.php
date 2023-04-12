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
 * @package     AuroraExtensions\SimpleReturns\Model\Repository
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Repository;

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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getById(int $rmaId): SimpleReturnInterface
    {
        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();
        $this->simpleReturnResource->load($rma, $rmaId);

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
     * {@inheritdoc}
     */
    public function save(SimpleReturnInterface $rma): int
    {
        $this->simpleReturnResource->save($rma);
        return (int) $rma->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SimpleReturnInterface $rma): bool
    {
        return $this->deleteById((int) $rma->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(int $rmaId): bool
    {
        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();
        $rma->setId($rmaId);
        return (bool) $this->simpleReturnResource->delete($rma);
    }
}

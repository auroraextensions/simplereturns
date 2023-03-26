<?php
/**
 * LabelRepository.php
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
use AuroraExtensions\SimpleReturns\Api\Data\LabelInterface;
use AuroraExtensions\SimpleReturns\Api\Data\LabelInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\LabelSearchResultsInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\LabelRepositoryInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Label as LabelResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Label\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

use function __;

class LabelRepository implements LabelRepositoryInterface
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

    /** @var LabelInterfaceFactory $labelFactory */
    private $labelFactory;

    /** @var LabelResource $labelResource */
    private $labelResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param LabelSearchResultsInterfaceFactory $searchResultsFactory
     * @param ExceptionFactory $exceptionFactory
     * @param LabelInterfaceFactory $labelFactory
     * @param LabelResource $labelResource
     * @return void
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        LabelSearchResultsInterfaceFactory $searchResultsFactory,
        ExceptionFactory $exceptionFactory,
        LabelInterfaceFactory $labelFactory,
        LabelResource $labelResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
    }

    /**
     * @param PackageInterface $package
     * @return LabelInterface
     * @throws NoSuchEntityException
     */
    public function get(PackageInterface $package): LabelInterface
    {
        /** @var LabelInterface $label */
        $label = $this->labelFactory->create();
        $this->labelResource->load(
            $label,
            $package->getId(),
            'pkg_id'
        );

        if (!$label->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate label(s) for the requested package.')
            );
            throw $exception;
        }

        return $label;
    }

    /**
     * @param int $id
     * @return LabelInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): LabelInterface
    {
        /** @var LabelInterface $label */
        $label = $this->labelFactory->create();
        $this->labelResource->load($label, $id);

        if (!$label->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate label(s) for the requested package.')
            );
            throw $exception;
        }

        return $label;
    }

    /**
     * @param LabelInterface $label
     * @return int
     */
    public function save(LabelInterface $label): int
    {
        $this->labelResource->save($label);
        return (int) $label->getId();
    }

    /**
     * @param LabelInterface $label
     * @return bool
     */
    public function delete(LabelInterface $label): bool
    {
        return $this->deleteById((int) $label->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        /** @var LabelInterface $label */
        $label = $this->labelFactory->create();
        $label->setId($id);
        return (bool) $this->labelResource->delete($label);
    }
}

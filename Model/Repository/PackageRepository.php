<?php
/**
 * PackageRepository.php
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
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageSearchResultsInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package as PackageResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

use function __;

class PackageRepository implements PackageRepositoryInterface
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

    /** @var PackageInterfaceFactory $packageFactory */
    private $packageFactory;

    /** @var PackageResource $packageResource */
    private $packageResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param PackageSearchResultsInterfaceFactory $searchResultsFactory
     * @param ExceptionFactory $exceptionFactory
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageResource $packageResource
     * @return void
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        PackageSearchResultsInterfaceFactory $searchResultsFactory,
        ExceptionFactory $exceptionFactory,
        PackageInterfaceFactory $packageFactory,
        PackageResource $packageResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->packageFactory = $packageFactory;
        $this->packageResource = $packageResource;
    }

    /**
     * {@inheritdoc}
     */
    public function get(SimpleReturnInterface $rma): PackageInterface
    {
        /** @var PackageInterface $package */
        $package = $this->packageFactory->create();
        $this->packageResource->load(
            $package,
            $rma->getId(),
            'rma_id'
        );

        if (!$package->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate package(s) for the requested RMA.')
            );
            throw $exception;
        }

        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $pkgId): PackageInterface
    {
        /** @var PackageInterface $package */
        $package = $this->packageFactory->create();
        $this->packageResource->load($package, $pkgId);

        if (!$package->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate package(s) for the requested RMA.')
            );
            throw $exception;
        }

        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function save(PackageInterface $package): int
    {
        $this->packageResource->save($package);
        return (int) $package->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PackageInterface $package): bool
    {
        return $this->deleteById((int) $package->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(int $pkgId): bool
    {
        /** @var PackageInterface $package */
        $package = $this->packageFactory->create();
        $package->setId($pkgId);
        return (bool) $this->packageResource->delete($package);
    }
}

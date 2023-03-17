<?php
/**
 * AttachmentRepository.php
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
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterfaceFactory;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Attachment as AttachmentResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Attachment\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

use function __;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var AbstractCollectionInterfaceFactory $collectionFactory
     * @var SearchResultsInterfaceFactory $searchResultsFactory
     * @method void addFilterGroupToCollection()
     * @method string getDirection()
     * @method SearchResultsInterface getList()
     */
    use AbstractRepositoryTrait;

    /** @var AttachmentInterfaceFactory $attachmentFactory */
    private $attachmentFactory;

    /** @var AttachmentResource $attachmentResource */
    private $attachmentResource;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param ExceptionFactory $exceptionFactory
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param AttachmentResource $attachmentResource
     * @return void
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        ExceptionFactory $exceptionFactory,
        AttachmentInterfaceFactory $attachmentFactory,
        AttachmentResource $attachmentResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResource = $attachmentResource;
    }

    /**
     * @param string $token
     * @return AttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get(string $token): AttachmentInterface
    {
        /** @var AttachmentInterface $attachment */
        $attachment = $this->attachmentFactory->create();
        $this->attachmentResource->load(
            $attachment,
            $token,
            'token'
        );

        if (!$attachment->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate RMA attachment information.')
            );
            throw $exception;
        }

        return $attachment;
    }

    /**
     * @param int $id
     * @return AttachmentInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): AttachmentInterface
    {
        /** @var AttachmentInterface $attachment */
        $attachment = $this->attachmentFactory->create();
        $this->attachmentResource->load($attachment, $id);

        if (!$attachment->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate RMA attachment information.')
            );
            throw $exception;
        }

        return $attachment;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return int
     */
    public function save(AttachmentInterface $attachment): int
    {
        $this->attachmentResource->save($attachment);
        return (int) $attachment->getId();
    }

    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function delete(AttachmentInterface $attachment): bool
    {
        return $this->deleteById((int) $attachment->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        /** @var AttachmentInterface $attachment */
        $attachment = $this->attachmentFactory->create();
        $attachment->setId($id);
        return (bool) $this->attachmentResource->delete($attachment);
    }
}

<?php
/**
 * AttachmentRepository.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\RepositoryModel;

use AuroraExtensions\SimpleReturns\{
    Api\AttachmentRepositoryInterface,
    Api\Data\AttachmentInterface,
    Api\Data\AttachmentInterfaceFactory,
    Exception\ExceptionFactory,
    Model\DataModel\Attachment,
    Model\ResourceModel\Attachment as AttachmentResource,
    Model\ResourceModel\Attachment\CollectionFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\SearchResultsInterface,
    Api\SearchResultsInterfaceFactory,
    Exception\NoSuchEntityException
};

class AttachmentRepository implements AttachmentRepositoryInterface, ModuleComponentInterface
{
    use AbstractRepositoryTrait;

    /** @property CollectionFactory $collectionFactory */
    protected $collectionFactory;

    /** @property SearchResultsInterfaceFactory $searchResultsFactory */
    protected $searchResultsFactory;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property AttachmentInterfaceFactory $attachmentFactory */
    protected $attachmentFactory;

    /** @property AttachmentResource $attachmentResource */
    protected $attachmentResource;

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
            self::SQL_COLUMN_ATTACHMENT_TOKEN_FIELD
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

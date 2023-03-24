<?php
/**
 * Attachment.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\SearchModel
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\SearchModel;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\AttachmentRepositoryInterface,
    Api\Data\AttachmentInterface,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\FilterBuilder,
    Api\SearchCriteriaBuilder,
    Exception\LocalizedException,
    Exception\NoSuchEntityException
};

use function __;
use function array_values;

class Attachment implements ModuleComponentInterface
{
    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var FilterBuilder $filterBuilder */
    protected $filterBuilder;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param ExceptionFactory $exceptionFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return void
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        ExceptionFactory $exceptionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->exceptionFactory = $exceptionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $fields
     * @return AttachmentInterface[]
     */
    public function getRecordsByFields(array $fields = []): array
    {
        /** @var array $filters */
        $filters = [];

        /** @var string $field */
        /** @var mixed $value */
        foreach ($fields as $field => $value) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setValue($value)
                ->create();
        }

        try {
            /** @var AttachmentInterface[] $attachments */
            $attachments = $this->getRecordsByFilters($filters);

            if (!empty($attachments)) {
                return $attachments;
            }

            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                LocalizedException::class,
                __('Unable to locate any matching attachments.')
            );
            throw $exception;
        } catch (NoSuchEntityException | LocalizedException $e) {
            /* No action required. */
        }

        return [];
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getRecordsByFilters(array $filters = []): array
    {
        /** @var SearchCriteria $criteria */
        $criteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->create();

        /** @var AttachmentInterface[] $items */
        $items = $this->attachmentRepository
            ->getList($criteria)
            ->getItems();

        return array_values($items);
    }
}

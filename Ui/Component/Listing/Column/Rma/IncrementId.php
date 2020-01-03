<?php
/**
 * IncrementId.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column\Rma;

use Exception;
use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface
};
use Magento\Framework\{
    Exception\NoSuchEntityException,
    UrlInterface,
    View\Element\UiComponent\ContextInterface,
    View\Element\UiComponentFactory
};
use Magento\Ui\Component\Listing\Columns\Column;

class IncrementId extends Column
{
    /** @constant string COLUMN_KEY */
    public const COLUMN_KEY = 'increment_id';

    /** @constant string ENTITY_KEY */
    public const ENTITY_KEY = 'rma_id';

    /** @property SimpleReturnRepositoryInterface $rmaRepository */
    protected $rmaRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param SimpleReturnRepositoryInterface $rmaRepository
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        SimpleReturnRepositoryInterface $rmaRepository
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->rmaRepository = $rmaRepository;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            /** @var array $item */
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var int|string|null $rmaId */
                $rmaId = $item[static::ENTITY_KEY] ?? null;

                if ($rmaId !== null) {
                    $item[static::ENTITY_KEY] = $this->getIncrementId((int) $rmaId);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $rmaId
     * @return string|null
     */
    private function getIncrementId(int $rmaId): ?string
    {
        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->rmaRepository->getById($rmaId);

            return sprintf('%09d', $rma->getId());
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (Exception $e) {
            /* No action required. */
        }

        return null;
    }
}

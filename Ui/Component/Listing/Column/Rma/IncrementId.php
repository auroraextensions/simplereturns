<?php
/**
 * IncrementId.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column\Rma;

use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Throwable;

use function sprintf;

class IncrementId extends Column
{
    public const COLUMN_KEY = 'increment_id';
    public const ENTITY_KEY = 'rma_id';

    /** @var SimpleReturnRepositoryInterface $rmaRepository */
    private $rmaRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SimpleReturnRepositoryInterface $rmaRepository
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SimpleReturnRepositoryInterface $rmaRepository,
        array $components = [],
        array $data = []
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
                    $item[static::COLUMN_KEY] = $this->getIncrementId((int) $rmaId);
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
            return sprintf('#%09d', $rma->getId());
        } catch (Throwable $e) {
            return null;
        }
    }
}

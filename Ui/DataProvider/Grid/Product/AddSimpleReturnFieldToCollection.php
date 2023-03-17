<?php
/**
 * AddSimpleReturnFieldToCollection.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Product
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

class AddSimpleReturnFieldToCollection implements AddFieldToCollectionInterface
{
    /** @var ProductAttributeRepositoryInterface $attributeRepository */
    private $attributeRepository;

    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(
        Collection $collection,
        $field,
        $alias = null
    ) {
        try {
            /** @var ProductAttributeInterface $attribute */
            $attribute = $this->attributeRepository->get($field);

            /** @var string $condition */
            $condition = sprintf(
                '{{table}}.attribute_id=%s',
                $attribute->getAttributeId()
            );
            $collection->joinField(
                'simple_return',
                'catalog_product_entity_int',
                'value',
                'product_id=entity_id',
                $condition,
                'left'
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}

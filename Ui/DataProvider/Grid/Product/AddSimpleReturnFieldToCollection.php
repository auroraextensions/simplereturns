<?php
/**
 * AddSimpleReturnFieldToCollection.php
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

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Product;

use Exception;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\{
    Data\Collection,
    Exception\NoSuchEntityException
};
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use function sprintf;

class AddSimpleReturnFieldToCollection implements AddFieldToCollectionInterface
{
    /** @var ProductAttributeRepositoryInterface $attributeRepository */
    private $attributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @return void
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        try {
            /** @var AttributeInterface $attribute */
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
        } catch (NoSuchEntityException | Exception $e) {
            /* No action required. */
        }
    }
}

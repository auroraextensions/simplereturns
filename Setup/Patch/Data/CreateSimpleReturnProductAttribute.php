<?php
/**
 * CreateSimpleReturnProductAttribute.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Setup\Patch\Data
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Setup\Patch\Data;

use AuroraExtensions\ModuleComponents\Model\Eav\Setup\AbstractEavSetup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;

class CreateSimpleReturnProductAttribute extends AbstractEavSetup implements
    DataPatchInterface,
    PatchRevertableInterface
{
    private const ATTR_CODE = 'simple_return';

    /** @var mixed[] $eavConfig */
    private $eavConfig;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     * @param mixed[] $eavConfig
     * @return void
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger,
        array $eavConfig = []
    ) {
        parent::__construct(
            $eavSetupFactory->create(['setup' => $moduleDataSetup]),
            $logger
        );
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->addAttribute(
            Product::ENTITY,
            self::ATTR_CODE,
            $this->eavConfig
        );
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->removeAttribute(
            Product::ENTITY,
            self::ATTR_CODE
        );
    }
}

<?php
/**
 * AddReturnLabelAllowedProductAttribute.php
 *
 * Add return_label_allowed product attribute.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
namespace AuroraExtensions\Returns\Setup\Patch\Data;

use AuroraExtensions\Returns\Shared\ModuleComponentInterface;
use Magento\{
    Catalog\Model\Product,
    Eav\Model\Entity\Attribute\ScopedAttributeInterface,
    Eav\Model\Entity\Attribute\Source\Boolean as SourceBoolean,
    Eav\Setup\EavSetupFactory,
    Framework\Setup\ModuleDataSetupInterface,
    Framework\Setup\Patch\DataPatchInterface,
    Framework\Setup\Patch\PatchRevertableInterface
};

class AddReturnLabelAllowedProductAttribute
    implements DataPatchInterface, PatchRevertableInterface, ModuleComponentInterface
{
    /** @property EavSetupFactory $eavSetupFactory */
    protected $eavSetupFactory;

    /** @property ModuleDataSetupInterface $moduleDataSetup */
    protected $moduleDataSetup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @return void
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_RETURN_LABEL_ALLOWED,
            [
                'type'             => 'int',
                'input'            => 'boolean',
                'label'            => self::ATTRIBUTE_LABEL_RETURN_LABEL_ALLOWED,
                'global'           => ScopedAttributeInterface::SCOPE_GLOBAL,
                'frontend'         => '',
                'source'           => SourceBoolean::class,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'default'          => 0,
                'searchable'       => false,
                'filterable'       => false,
                'comparable'       => false,
                'visible_on_front' => false,
                'unique'           => false,
                'apply_to'         => '',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->removeAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_RETURN_LABEL_ALLOWED
        );
    }
}

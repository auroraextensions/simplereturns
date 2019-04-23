<?php
/** 
 * Label.php
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
namespace AuroraExtensions\Returns\ViewModel;

use AuroraExtensions\Returns\{
    Helper\Config as ConfigHelper,
    Model\Label as LabelModel,
    Shared\ModuleComponentInterface
};

use Magento\Framework\{
    DataObject,
    View\Element\Block\ArgumentInterface
};

class Label extends DataObject implements ArgumentInterface, ModuleComponentInterface
{
    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property array $errors */
    protected $errors = [];

    /** @property LabelModel $labelModel */
    protected $labelModel;

    /**
     * @param ConfigHelper $configHelper
     * @param LabelModel $labelModel
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        LabelModel $labelModel,
        array $data = []
    ) {
        parent::__construct($data);
        $this->configHelper = $configHelper;
        $this->labelModel = $labelModel;
    }

    /**
     * Check for label image.
     *
     * @return bool
     */
    public function hasLabel()
    {
        /** @var string $cacheKey */
        $cacheKey = $this->labelModel->getCacheKey($this->getOrder());

        /** @var string|null $image */
        $image = $this->labelModel->hasCachedImage($cacheKey)
            ? $this->labelModel->getCachedImage($cacheKey)
            : $this->labelModel->getImage();

        return (!empty($image) && $image !== null);
    }

    /**
     * Get error messages from label creation.
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errors;
    }

    /**
     * Get return form URL for store.
     *
     * @return string
     */
    public function getReturnFormUrl()
    {
        return $this->configHelper->getReturnFormUrl($this->getOrder()->getStoreId());
    }

    /**
     * Get encoded label string as data URI.
     *
     * @return string|null
     */
    public function getLabelEncodedDataUri(): ?string
    {
        /** @var string $cacheKey */
        $cacheKey = $this->labelModel->getCacheKey($this->getOrder());

        /** @var string|null $image */
        $image = $this->labelModel->hasCachedImage($cacheKey)
            ? $this->labelModel->getCachedImage($cacheKey)
            : $this->labelModel->getImage();

        return self::PREFIX_DATAURI . $image;
    }
}

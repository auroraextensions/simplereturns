<?php
/**
 * IndexView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Label;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\DataModel\Label as LabelModel,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};

class IndexView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property array $errors */
    protected $errors = [];

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder
        );
    }

    /**
     * Check for label image.
     *
     * @return bool
     */
    public function hasLabel()
    {
        return false;
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
        return '';
    }
}

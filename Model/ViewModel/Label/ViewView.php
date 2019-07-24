<?php
/**
 * ViewView.php
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
    Api\Data\LabelInterface,
    Api\LabelManagementInterface,
    Api\LabelRepositoryInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\{
    Api\Data\OrderInterface,
    Api\OrderRepositoryInterface
};

class ViewView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property LabelManagementInterface $labelManagement */
    protected $labelManagement;

    /** @property LabelRepositoryInterface $labelRepository */
    protected $labelRepository;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param LabelManagementInterface $labelManagement
     * @param LabelRepositoryInterface $labelRepository
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        LabelManagementInterface $labelManagement,
        LabelRepositoryInterface $labelRepository,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        PackageRepositoryInterface $packageRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder
        );

        $this->labelManagement = $labelManagement;
        $this->labelRepository = $labelRepository;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->packageRepository = $packageRepository;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * @return string|null
     */
    public function getLabelDataUri(): ?string
    {
        /** @var LabelInterface|null $label */
        $label = $this->getLabel();

        if ($label !== null) {
            return $this->labelManagement->getImageDataUri($label);
        }

        return null;
    }

    /**
     * @return LabelInterface|null
     */
    public function getLabel(): ?LabelInterface
    {
        /** @var int|string|null $labelId */
        $labelId = $this->request->getParam(self::PARAM_LABEL_ID);
        $labelId = $labelId !== null && is_numeric($labelId)
            ? (int) $labelId
            : null;

        if ($labelId !== null) {
            try {
                return $this->labelRepository->getById($labelId);
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        /** @var LabelInterface|null $label */
        $label = $this->getLabel();

        if ($label !== null) {
            return true;
        }

        return false;
    }

    /**
     * Get return form URL for store.
     *
     * @return string|null
     */
    public function getReturnFormUrl(): ?string
    {
        return $this->moduleConfig->getReturnFormUrl();
    }
}

<?php
/**
 * ViewView.php
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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\SimpleReturns\{
    Api\Data\LabelInterface,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\LabelManagementInterface,
    Api\LabelRepositoryInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\Api\Data\OrderInterface;

class ViewView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property DirectoryHelper $directoryHelper */
    protected $directoryHelper;

    /** @property LabelInterface $label */
    protected $label;

    /** @property LabelManagementInterface $labelManagement */
    protected $labelManagement;

    /** @property LabelRepositoryInterface $labelRepository */
    protected $labelRepository;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderInterface $order */
    protected $order;

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @property PackageInterface $package */
    protected $package;

    /** @property PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @property SimpleReturnInterface $rma */
    protected $rma;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param DirectoryHelper $directoryHelper
     * @param LabelManagementInterface $labelManagement
     * @param LabelRepositoryInterface $labelRepository
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        DirectoryHelper $directoryHelper,
        LabelManagementInterface $labelManagement,
        LabelRepositoryInterface $labelRepository,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        PackageRepositoryInterface $packageRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->directoryHelper = $directoryHelper;
        $this->labelManagement = $labelManagement;
        $this->labelRepository = $labelRepository;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
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
     * @return string
     */
    public function getShippingCarrier(): string
    {
        return $this->moduleConfig->getShippingCarrier();
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->moduleConfig->getShippingMethod();
    }

    /**
     * Get frontend label for field type by key(s).
     *
     * @param string $type
     * @param string $key
     * @param string|null $subkey
     * @param string
     */
    public function getFrontLabel(
        string $type,
        string $key,
        string $subkey = null
    ): string
    {
        /** @var array $labels */
        $labels = $this->moduleConfig
            ->getSettings()
            ->getData($type);

        /** @var string|array $label */
        $label = $labels[$key] ?? $key;

        if ($subkey !== null) {
            $label = is_array($label) && isset($label[$subkey])
                ? $label[$subkey]
                : $label;
        }

        return $label;
    }

    /**
     * @return PackageInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPackage(): ?PackageInterface
    {
        if ($this->package !== null) {
            return $this->package;
        }

        /** @var int|string|null $pkgId */
        $pkgId = $this->request->getParam(self::PARAM_PKG_ID);
        $pkgId = $pkgId !== null && is_numeric($pkgId)
            ? (int) $pkgId
            : null;

        if ($pkgId !== null) {
            /** @var string|null $pkgToken */
            $pkgToken = $this->request->getParam(self::PARAM_TOKEN);
            $pkgToken = $pkgToken !== null && Tokenizer::isHex($pkgToken)
                ? $pkgToken
                : null;

            if ($pkgToken !== null) {
                try {
                    /** @var PackageInterface $package */
                    $package = $this->packageRepository->getById($pkgId);

                    if (Tokenizer::isEqual($pkgToken, $package->getToken())) {
                        $this->package = $package;

                        return $package;
                    }

                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );

                    throw $exception;
                } catch (NoSuchEntityException $e) {
                    /* No action required. */
                } catch (LocalizedException $e) {
                    /* No action required. */
                }
            }
        }

        return null;
    }

    /**
     * @return LabelInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getLabel(): ?LabelInterface
    {
        if ($this->label !== null) {
            return $this->label;
        }

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            /** @var int $labelId */
            $labelId = (int) $package->getLabelId();

            try {
                /** @var LabelInterface $label */
                $label = $this->labelRepository->getById($labelId);

                if ($label->getId()) {
                    $this->label = $label;

                    return $label;
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * Get associated SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        if ($this->rma !== null) {
            return $this->rma;
        }

        /** @var PackageInterface $package */
        $package = $this->getPackage();

        if ($package !== null) {
            /** @var int $pkgId */
            $pkgId = (int) $package->getRmaId();

            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($pkgId);

                if ($rma->getId()) {
                    $this->rma = $rma;

                    return $rma;
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return OrderInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        if ($this->order !== null) {
            return $this->order;
        }

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var array $fields */
            $fields = [
                'entity_id' => $rma->getOrderId(),
            ];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter->getOrdersByFields($fields);

                if (!empty($orders)) {
                    $this->order = $orders[0];

                    return $orders[0];
                }

                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __('Unable to locate any matching orders.')
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPackageWeight(): string
    {
        /** @var Shipment $shipment */
        $shipment = $this->getShipment();

        /** @var float|string $weight */
        $weight = $shipment->getWeight() ?? $this->moduleConfig->getPackageWeight();

        return number_format((float) $weight, 2);
    }

    /**
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->getOrder()
            ->getShipmentsCollection()
            ->getFirstItem();
    }

    /**
     * @return string
     */
    public function getWeightUnits(): string
    {
        return $this->directoryHelper->getWeightUnit();
    }

    /**
     * @return string
     */
    public function getGenerateLabelUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            $params['pkg_id'] = $package->getId();
            $params['token'] = $package->getToken();
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/label/generate',
            $params
        );
    }

    /**
     * @return string
     */
    public function getViewRmaUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            $params['rma_id'] = $rma->getId();
            $params['token'] = $rma->getToken();
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/rma/view',
            $params
        );
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
     * @return bool
     */
    public function hasPackage(): bool
    {
        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            return true;
        }

        return false;
    }
}

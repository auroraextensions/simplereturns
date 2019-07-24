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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\SimpleReturns\{
    Api\Data\LabelInterface,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\LabelRepositoryInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\AdapterModel\Security\Token as Tokenizer,
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

    /** @property LabelRepositoryInterface $labelRepository */
    protected $labelRepository;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

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
     * @param DirectoryHelper $directoryHelper
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
        $this->labelRepository = $labelRepository;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->packageRepository = $packageRepository;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * @return string
     */
    public function getShippingCarrier(): string
    {
        return $this->moduleConfig->getShippingCarrier();
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontendLabel(string $type, string $key): string
    {
        /** @var array $labels */
        $labels = $this->moduleConfig->getSettings()->getData($type);

        return $labels[$key] ?? $key;
    }

    /**
     * @return PackageInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPackage(): ?PackageInterface
    {
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
        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            /** @var int $labelId */
            $labelId = (int) $package->getLabelId();

            try {
                /** @var LabelInterface $label */
                $label = $this->labelRepository->getById($labelId);

                if ($label->getId()) {
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
        /** @var PackageInterface $package */
        $package = $this->getPackage();

        if ($package !== null) {
            /** @var int $pkgId */
            $pkgId = (int) $package->getRmaId();

            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($pkgId);

                if ($rma->getId()) {
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
        $weight = (float) $weight;

        return number_format($weight, 2);
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
    public function getViewLabelUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var LabelInterface|null $label */
        $label = $this->getLabel();

        if ($label !== null) {
            $params['label_id'] = $label->getId();
            $params['token'] = $label->getToken();
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/label/view',
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

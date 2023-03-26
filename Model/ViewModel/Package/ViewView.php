<?php
/**
 * ViewView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\LabelInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\LabelManagementInterface;
use AuroraExtensions\SimpleReturns\Api\LabelRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use AuroraExtensions\SimpleReturns\Helper\Config as ConfigHelper;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\ViewModel\AbstractView;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

use function __;
use function array_shift;
use function is_numeric;
use function number_format;

class ViewView extends AbstractView implements ArgumentInterface
{
    /**
     * @var ConfigInterface $moduleConfig
     * @method ConfigInterface getConfig()
     */
    use ModuleConfigTrait;

    private const PARAM_PKG_ID = 'pkg_id';
    private const PARAM_TOKEN = 'token';

    /** @var DirectoryHelper $directoryHelper */
    private $directoryHelper;

    /** @var LabelInterface $label */
    private $label;

    /** @var LabelManagementInterface $labelManagement */
    private $labelManagement;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var LabelRepositoryInterface $labelRepository */
    private $labelRepository;

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var OrderInterface $order */
    private $order;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var PackageInterface $package */
    private $package;

    /** @var PackageRepositoryInterface $packageRepository */
    private $packageRepository;

    /** @var SimpleReturnInterface $rma */
    private $rma;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param DirectoryHelper $directoryHelper
     * @param LabelManagementInterface $labelManagement
     * @param LabelManager $labelManager
     * @param LabelRepositoryInterface $labelRepository
     * @param MessageManagerInterface $messageManager
     * @param ConfigInterface $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        DirectoryHelper $directoryHelper,
        LabelManagementInterface $labelManagement,
        LabelManager $labelManager,
        LabelRepositoryInterface $labelRepository,
        MessageManagerInterface $messageManager,
        ConfigInterface $moduleConfig,
        OrderAdapter $orderAdapter,
        PackageRepositoryInterface $packageRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        array $data = []
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
        $this->labelManager = $labelManager;
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
        return $label !== null
            ? $this->labelManagement->getImageDataUri($label) : null;
    }

    /**
     * @return string
     */
    public function getShippingCarrier(): string
    {
        return $this->getConfig()->getShippingCarrier();
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->getConfig()->getShippingMethod();
    }

    /**
     * Get frontend label for field type by key(s).
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(
        string $type,
        string $key
    ): string {
        return $this->labelManager
            ->getLabel($type, $key) ?? $key;
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
        $pkgId = is_numeric($pkgId) ? (int) $pkgId : null;

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($pkgId !== null && $token !== null) {
            try {
                /** @var PackageInterface $package */
                $package = $this->packageRepository->getById($pkgId);

                if (!Tokenizer::isEqual($token, $package->getToken())) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );
                    throw $exception;
                }

                $this->package = $package;
                return $package;
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
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

                if (!$label->getId()) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );
                    throw $exception;
                }

                $this->label = $label;
                return $label;
            } catch (NoSuchEntityException | LocalizedException $e) {
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
            /** @var int $rmaId */
            $rmaId = (int) $package->getRmaId();

            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($rmaId);

                if (!$rma->getId()) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );
                    throw $exception;
                }

                $this->rma = $rma;
                return $rma;
            } catch (NoSuchEntityException | LocalizedException $e) {
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
            $fields = ['entity_id' => $rma->getOrderId()];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter
                    ->getOrdersByFields($fields);

                if (empty($orders)) {
                    /** @var NoSuchEntityException $exception */
                    $exception = $this->exceptionFactory->create(
                        NoSuchEntityException::class,
                        __('Unable to locate any matching orders.')
                    );
                    throw $exception;
                }

                $this->order = array_shift($orders);
                return $this->order;
            } catch (NoSuchEntityException | LocalizedException $e) {
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
        /** @var OrderInterface $order */
        $order = $this->getOrder();

        /** @var float $weight */
        $weight = (float)(
            $order->getWeight()
                ?? $this->getConfig()->getPackageWeight()
        );
        return number_format($weight, 2);
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
        $params = ['_secure' => true];

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            $params += [
                'pkg_id' => $package->getId(),
                'token' => $package->getToken(),
            ];
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
        $params = ['_secure' => true];

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            $params += [
                'rma_id' => $rma->getId(),
                'token' => $rma->getToken(),
            ];
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
        return $label !== null
            ? (bool) $label->getId() : false;
    }

    /**
     * @return bool
     */
    public function hasPackage(): bool
    {
        /** @var PackageInterface|null $package */
        $package = $this->getPackage();
        return $package !== null
            ? (bool) $package->getId() : false;
    }
}

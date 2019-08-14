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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\SimpleReturns\{
    Api\Data\LabelInterface,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\LabelRepositoryInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Security\Token as Tokenizer,
    Model\SearchModel\Attachment as AttachmentAdapter,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    Serialize\Serializer\Json,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\{
    Api\Data\OrderInterface,
    Api\OrderRepositoryInterface
};
use Magento\Store\Model\StoreManagerInterface;

class ViewView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property AttachmentAdapter $attachmentAdapter */
    protected $attachmentAdapter;

    /** @property LabelInterface $label */
    protected $label;

    /** @property LabelRepositoryInterface $labelRepository */
    protected $labelRepository;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderInterface $order */
    protected $order;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property PackageInterface $package */
    protected $package;

    /** @property PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @property SimpleReturnInterface $rma */
    protected $rma;

    /** @property Json $serializer */
    protected $serializer;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @property Tokenizer $tokenizer */
    protected $tokenizer;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param AttachmentAdapter $attachmentAdapter
     * @param LabelRepositoryInterface $labelRepository
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param Json $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @param Tokenizer $tokenizer
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        AttachmentAdapter $attachmentAdapter,
        LabelRepositoryInterface $labelRepository,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        PackageRepositoryInterface $packageRepository,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager,
        Tokenizer $tokenizer
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->attachmentAdapter = $attachmentAdapter;
        $this->labelRepository = $labelRepository;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->packageRepository = $packageRepository;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @return string
     */
    public function getCreatePackageUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);

        if ($rmaId !== null) {
            $params['rma_id'] = $rmaId;
        }

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);

        if ($token !== null) {
            $params['token'] = $token;
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/package/create',
            $params
        );
    }

    /**
     * @return string
     */
    public function getViewCustomerUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var OrderInterface|null $order */
        $order = $this->getOrder();

        if ($order !== null) {
            /** @var bool $isGuest */
            $isGuest = (bool) $order->getCustomerIsGuest();

            if (!$isGuest) {
                /** @var int|string|null $customerId */
                $customerId = $order->getCustomerId();

                if ($customerId !== null) {
                    $params['id'] = $customerId;
                }
            }
        }

        return $this->urlBuilder->getUrl(
            'customer/index/edit',
            $params
        );
    }

    /**
     * @return string
     */
    public function getViewPackageUrl(): string
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
            'simplereturns/package/view',
            $params
        );
    }

    /**
     * @return string
     */
    public function getEditRmaUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);

        if ($rmaId !== null) {
            $params['rma_id'] = $rmaId;
        }

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);

        if ($token !== null) {
            $params['token'] = $token;
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/rma/edit',
            $params
        );
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(string $type, string $key): string
    {
        /** @var array $labels */
        $labels = $this->moduleConfig->getSettings()->getData($type);

        return $labels[$key] ?? $key;
    }

    /**
     * Get associated SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        if ($this->rma !== null) {
            return $this->rma;
        }

        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if (is_int($rmaId)) {
            /** @var string|null $token */
            $token = $this->request->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                try {
                    /** @var SimpleReturnInterface $rma */
                    $rma = $this->simpleReturnRepository->getById($rmaId);

                    if (Tokenizer::isEqual($token, $rma->getToken())) {
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
        }

        return null;
    }

    /**
     * Get associated order.
     *
     * @return OrderInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        if ($this->order !== null) {
            return $this->order;
        }

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            try {
                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($rma->getOrderId());

                if ($order->getId()) {
                    $this->order = $order;

                    return $order;
                }
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return PackageInterface|null
     */
    public function getPackage(): ?PackageInterface
    {
        if ($this->package !== null) {
            return $this->package;
        }

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var int|string|null $pkgId */
            $pkgId = $rma->getPackageId();
            $pkgId = $pkgId !== null && is_numeric($pkgId)
                ? (int) $pkgId
                : null;

            if ($pkgId !== null) {
                try {
                    /** @var PackageInterface $package */
                    $package = $this->packageRepository->getById($pkgId);

                    if ($package->getId()) {
                        $this->package = $package;

                        return $package;
                    }
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
     */
    public function getLabel(): ?LabelInterface
    {
        if ($this->label !== null) {
            return $this->label;
        }

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            try {
                /** @var LabelInterface $label */
                $label = $this->labelRepository->get($package);

                if ($label->getId()) {
                    $this->label = $label;

                    return $label;
                }
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        /** @var array $results */
        $results = [];

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var string $baseUrl */
            $baseUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $baseUrl = rtrim($baseUrl, '/');

            /** @var string $mediaUrl */
            $mediaUrl = $baseUrl . self::SAVE_PATH;
            $mediaUrl = rtrim($mediaUrl, '/');

            /** @var array $attachments */
            $attachments = $this->attachmentAdapter
                ->getRecordsByFields(['rma_id' => $rma->getId()]);

            /** @var AttachmentInterface $attachment */
            foreach ($attachments as $attachment) {
                $results[] = ($mediaUrl . $attachment->getPath());
            }
        }

        return $results;
    }

    /**
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return (bool)(count($this->getAttachments()));
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

    /**
     * @return bool
     */
    public function hasSimpleReturn(): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isRequestApproved(): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var string $status */
            $status = $rma->getStatus()
                ?? ModuleConfig::DEFAULT_RMA_STATUS_CODE;

            return ($status === 'approved');
        }

        return false;
    }
}

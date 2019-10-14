<?php
/**
 * EditView.php
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
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\AttachmentManagementInterface,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Security\Token as Tokenizer,
    Model\SearchModel\Attachment as AttachmentAdapter,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Data\Form\FormKey,
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

class EditView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property AttachmentAdapter $attachmentAdapter */
    protected $attachmentAdapter;

    /** @property AttachmentManagementInterface $attachmentManagement */
    protected $attachmentManagement;

    /** @property AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @property FormKey $formKey */
    protected $formKey;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderInterface $order */
    protected $order;

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property SimpleReturnInterface $rma */
    protected $rma;

    /** @property Json $serializer */
    protected $serializer;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param AttachmentAdapter $attachmentAdapter
     * @param AttachmentManagementInterface $attachmentManagement
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param FormKey $formKey
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        AttachmentAdapter $attachmentAdapter,
        AttachmentManagementInterface $attachmentManagement,
        AttachmentRepositoryInterface $attachmentRepository,
        FormKey $formKey,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        OrderRepositoryInterface $orderRepository,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->attachmentAdapter = $attachmentAdapter;
        $this->attachmentManagement = $attachmentManagement;
        $this->attachmentRepository = $attachmentRepository;
        $this->formKey = $formKey;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $route
     * @return string
     */
    public function getPostActionUrl(
        string $route = self::ROUTE_SIMPLERETURNS_RMA_EDITPOST
    ): string
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

        return $this->urlBuilder->getUrl($route, $params);
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
            'simplereturns/rma/view',
            $params
        );
    }

    /**
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

        if ($rmaId !== null) {
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
     * @return OrderInterface|null
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
     * @return array
     */
    public function getFiles(): array
    {
        /** @var array $files */
        $files = [];

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            try {
                /** @var AttachmentInterface[] $attachments */
                $attachments = $this->attachmentAdapter
                    ->getRecordsByFields(['rma_id' => $rma->getId()]);

                /** @var AttachmentInterface $attachment */
                foreach ($attachments as $attachment) {
                    /** @var string $dataUri */
                    $dataUri = $this->attachmentManagement
                        ->getFileDataUri($attachment);

                    /** @var string $fileUrl */
                    $fileUrl = $this->attachmentManagement
                        ->getFileUrl($attachment);

                    $files[] = [
                        'blob'  => $dataUri,
                        'name'  => $attachment->getFilename(),
                        'size'  => $attachment->getFilesize(),
                        'token' => $attachment->getToken(),
                        'type'  => $attachment->getMimeType(),
                        'url'   => $fileUrl,
                    ];
                }
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            } catch (LocalizedException $e) {
                /* No action required. */
            }
        }

        return $files;
    }

    /**
     * @return string
     */
    public function getSerializedFiles(): string
    {
        return $this->serializer->serialize($this->getFiles());
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->moduleConfig->getReasons();
    }

    /**
     * @return array
     */
    public function getResolutions(): array
    {
        return $this->moduleConfig->getResolutions();
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
}

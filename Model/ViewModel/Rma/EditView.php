<?php
/**
 * EditView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\AttachmentManagementInterface,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
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

use function is_numeric;

class EditView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @var AttachmentAdapter $attachmentAdapter */
    protected $attachmentAdapter;

    /** @var AttachmentManagementInterface $attachmentManagement */
    protected $attachmentManagement;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var FormKey $formKey */
    protected $formKey;

    /** @var MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @var ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @var OrderInterface $order */
    protected $order;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @var SimpleReturnInterface $rma */
    protected $rma;

    /** @var Json $serializer */
    protected $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @var string $route */
    private $route;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
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
     * @param array $data
     * @param string $route
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
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
        StoreManagerInterface $storeManager,
        array $data = [],
        string $route = self::ROUTE_SIMPLERETURNS_RMA_EDITPOST
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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPostActionUrl(
        string $route = '',
        array $params = []
    ): string {
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

        return parent::getPostActionUrl($this->route, $params);
    }

    /**
     * @return string
     */
    public function getViewRmaUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

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
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($rmaId);

                if (!Tokenizer::isEqual($token, $rma->getToken())) {
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
            } catch (NoSuchEntityException | LocalizedException $e) {
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
            } catch (NoSuchEntityException | LocalizedException $e) {
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
        return $rma !== null ? (bool) $rma->getId() : false;
    }
}

<?php
/**
 * EditPost.php
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

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\System\ModuleConfigTrait,
    Exception\ExceptionFactory,
    Model\Security\Token as Tokenizer,
    Model\Email\Transport\Customer as EmailTransport,
    Shared\Component\LabelFormatterTrait,
    Shared\ModuleComponentInterface,
    Csi\System\Module\ConfigInterface
};
use Magento\Backend\{
    App\Action,
    App\Action\Context
};
use Magento\Framework\{
    App\Action\HttpPostActionInterface,
    Controller\Result\JsonFactory as ResultJsonFactory,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Escaper,
    Event\ManagerInterface as EventManagerInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Serialize\Serializer\Json as JsonSerializer,
    UrlInterface
};
use Magento\Sales\Api\OrderRepositoryInterface;

class EditPost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use ModuleConfigTrait, LabelFormatterTrait;

    /** @property EmailTransport $emailTransport */
    protected $emailTransport;

    /** @property Escaper $escaper */
    protected $escaper;

    /** @property EventManagerInterface $eventManager */
    protected $eventManager;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @property JsonSerializer $serializer */
    protected $serializer;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ConfigInterface $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param ResultJsonFactory $resultJsonFactory
     * @param JsonSerializer $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        Context $context,
        EmailTransport $emailTransport,
        Escaper $escaper,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        ConfigInterface $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        ResultJsonFactory $resultJsonFactory,
        JsonSerializer $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var array $response */
        $response = [];

        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid method: Must be POST request.'),
            ]);

            return $resultJson;
        }

        if (!$this->formKeyValidator->validate($request)) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid form key.'),
            ]);

            return $resultJson;
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if ($rmaId !== null) {
            /** @var string|null $token */
            $token = $request->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                /** @var string|null $status */
                $status = $request->getPostValue('status');
                $status = $status !== null ? trim($status) : null;

                /** @var string|null $reason */
                $reason = $request->getPostValue('reason');
                $reason = $reason !== null ? trim($reason) : null;

                /** @var string|null $resolution */
                $resolution = $request->getPostValue('resolution');
                $resolution = $resolution !== null ? trim($resolution) : null;

                /** @var string|null $comments */
                $comments = $request->getPostValue('comments');
                $comments = $resolution !== null && !empty($comments)
                    ? $this->escaper->escapeHtml($comments)
                    : null;

                try {
                    /** @var SimpleReturnInterface $rma */
                    $rma = $this->simpleReturnRepository->getById($rmaId);

                    if ($rma->getId()) {
                        $this->eventManager->dispatch(
                            'simplereturns_adminhtml_rma_edit_save_before',
                            [
                                'rma' => $rma,
                                'status' => $status,
                                'reason' => $reason,
                                'resolution' => $resolution,
                                'comments' => $comments,
                            ]
                        );

                        $this->simpleReturnRepository->save(
                            $rma->addData([
                                'status' => $status,
                                'reason' => $reason,
                                'resolution' => $resolution,
                                'comments' => $comments,
                            ])
                        );

                        $this->eventManager->dispatch(
                            'simplereturns_adminhtml_rma_edit_save_after',
                            [
                                'rma' => $rma,
                            ]
                        );

                        /** @var OrderInterface $order */
                        $order = $this->orderRepository->get($rma->getOrderId());

                        /** @var string $email */
                        $email = $order->getCustomerEmail();

                        /** @var string $name */
                        $name = $order->getCustomerName();

                        $this->emailTransport->send(
                            'simplereturns/customer/rma_request_status_update_email_template',
                            'simplereturns/customer/rma_request_status_update_email_identity',
                            [
                                'orderId' => $order->getRealOrderId(),
                                'frontId' => $rma->getFrontId(),
                                'reason' => $this->getFrontLabel('reasons', $rma->getReason()),
                                'resolution' => $this->getFrontLabel('resolutions', $rma->getResolution()),
                                'status' => $this->getFrontLabel('statuses', $rma->getStatus()),
                            ],
                            $email,
                            $name,
                            (int) $order->getStoreId()
                        );

                        /** @var string $viewUrl */
                        $viewUrl = $this->urlBuilder->getUrl(
                            'simplereturns/rma/view',
                            [
                                'rma_id' => $rmaId,
                                'token' => $token,
                                '_secure' => true,
                            ]
                        );

                        $resultJson->setData([
                            'success' => true,
                            'isSimpleReturnsAjax' => true,
                            'message' => __('Successfully updated RMA.'),
                            'viewUrl' => $viewUrl,
                        ]);
                        return $resultJson;
                    }
                } catch (NoSuchEntityException $e) {
                    $response = [
                        'error' => true,
                        'message' => $e->getMessage(),
                    ];
                } catch (LocalizedException $e) {
                    $response = [
                        'error' => true,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}

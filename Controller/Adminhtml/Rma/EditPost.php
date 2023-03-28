<?php
/**
 * EditPost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Throwable;

use function __;
use function is_numeric;
use function trim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends Action implements HttpPostActionInterface
{
    use ModuleConfigTrait;

    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var EmailTransport $emailTransport */
    private $emailTransport;

    /** @var Escaper $escaper */
    private $escaper;

    /** @var EventManagerInterface $eventManager */
    private $eventManager;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param LabelManager $labelManager
     * @param ConfigInterface $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
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
        LabelManager $labelManager,
        ConfigInterface $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        ResultJsonFactory $resultJsonFactory,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->labelManager = $labelManager;
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

        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
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
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
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
                ? $this->escaper->escapeHtml($comments) : null;

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
                        ['rma' => $rma]
                    );

                    /** @var OrderInterface $order */
                    $order = $this->orderRepository->get($rma->getOrderId());
                    $this->emailTransport->send(
                        'simplereturns/customer/rma_request_status_update_email_template',
                        'simplereturns/customer/rma_request_status_update_email_identity',
                        [
                            'orderId' => $order->getRealOrderId(),
                            'uuid' => $rma->getUuid(),
                            'reason' => $this->labelManager->getLabel('reason', $rma->getReason()),
                            'resolution' => $this->labelManager->getLabel('resolution', $rma->getResolution()),
                            'status' => $this->labelManager->getLabel('status', $rma->getStatus()),
                        ],
                        $order->getCustomerEmail(),
                        $order->getCustomerName(),
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
            } catch (Throwable $e) {
                $response = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}

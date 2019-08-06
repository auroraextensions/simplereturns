<?php
/**
 * Search.php
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

namespace AuroraExtensions\SimpleReturns\Controller\Rma\Attachment;

use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    Controller\Result\JsonFactory as ResultJsonFactory,
    View\Result\PageFactory
};

class Search extends Action implements HttpGetActionInterface
{
    /** @property ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @return void
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute simplereturns_rma_create action.
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $resultJson->setData(
            [
                'name' => 'cip_small_1.png',
                'path' => '/pub/media/simplereturns/c/i/cip_small_1.png',
                'size' => '21817',
            ]
        );

        return $resultJson;
    }
}

<?php
/**
 * View.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma;

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\SimpleReturns\{
    Model\ViewModel\Rma\ViewView as ViewModel,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    View\Result\PageFactory
};

use function __;

class View extends Action implements
    HttpGetActionInterface,
    ModuleComponentInterface
{
    use RedirectTrait;

    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @var ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->viewModel = $viewModel;
    }

    /**
     * Execute simplereturns_rma_view action.
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('View RMA Details')
        );

        if ($this->viewModel->hasSimpleReturn()) {
            return $resultPage;
        }

        return $this->getRedirectToPath(self::ROUTE_SALES_GUEST_VIEW);
    }
}

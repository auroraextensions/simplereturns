<?php
/**
 * Customer.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
 */
namespace AuroraExtensions\SimpleReturns\Model\Email\Transport;

use AuroraExtensions\SimpleReturns\{
    Model\Email\AbstractTransport,
    Shared\ModuleComponentInterface
};

class Customer extends AbstractTransport implements ModuleComponentInterface
{
    /**
     * Send email notification to customer.
     *
     * @param Customer $customer
     * @param string $template
     * @param string $sender
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     * @see Magento\Customer\Model\Customer::_sendEmailTemplate()
     * @see AuroraExtensions\SimpleReturns\Model\Customer\Customer::sendEmailTemplate()
     */
    public function sendEmailNotification(
        $customer,
        $template,
        $sender = self::XML_PATH_CUSTOMER_LOGIN_REQUEST_EMAIL_IDENTITY,
        $templateParams = [],
        $storeId = null
    ) {
        /** @var int $storeId */
        $storeId = $storeId ?? $customer->getStoreId();

        /* Send email notification via Customer\Customer::sendEmailTemplate(). */
        $customer->sendEmailTemplate($template, $sender, $templateParams, $storeId);

        return $this;
    }
}

/**
 * requirejs-config.js
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
var config = {
    map: {
        '*': {
            simpleReturnsRmaEditStatus: 'AuroraExtensions_SimpleReturns/js/rma/edit/status',
            simpleReturnsAjaxSaveRedirect: 'AuroraExtensions_SimpleReturns/js/form/redirect'
        }
    }
};

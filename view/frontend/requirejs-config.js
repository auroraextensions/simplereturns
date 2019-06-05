/**
 * requirejs-config.js
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
var config = {
    paths: {
        'jquery.print': 'AuroraExtensions_SimpleReturns/js/plugins/jquery.print'
    },
    shim: {
        'jquery.print': {
            'deps': ['jquery']
        }
    },
    map: {
        '*': {
            labelPrint: 'AuroraExtensions_SimpleReturns/js/print'
        }
    }
};

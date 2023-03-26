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
    paths: {
        'dropzone': 'AuroraExtensions_SimpleReturns/js/plugins/dropzone',
        'jquery.print': 'AuroraExtensions_SimpleReturns/js/plugins/jquery.print'
    },
    shim: {
        'jquery.print': {
            'deps': ['jquery']
        }
    },
    map: {
        '*': {
            simpleReturnsLabelPrint: 'AuroraExtensions_SimpleReturns/js/print',
            simpleReturnsDragAndDrop: 'AuroraExtensions_SimpleReturns/js/dnd'
        }
    }
};

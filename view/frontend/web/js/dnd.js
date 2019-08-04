/**
 * dnd.js
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
define([
    'jquery',
    'dropzone',
    'AuroraExtensions_SimpleReturns/js/url/builder',
    'AuroraExtensions_SimpleReturns/js/url/parser'
], function ($, Dropzone, urlBuilder, urlParser) {
    'use strict';

    $.widget('mage.simpleReturnsDragAndDrop', {
        options: {
            attachKey: '',
            dropzone: '.dropzone',
            routePath: 'simplereturns/rma_attachment/createPost'
        },
        /**
         * @return {void}
         */
        _create: function () {
            var attachKey, settings, targetUrl;

            /** @var {String} targetUrl */
            targetUrl = urlBuilder.getUrl(
                this.options.routePath,
                {
                    'rma_id': urlParser.getParamValue('rma_id'),
                    'token': urlParser.getParamValue('token'),
                    'attach_key': attachKey
                }
            );

            /** @var {Object} settings */
            settings = {
                url: targetUrl,
                uploadMultiple: true,
                clickable: true
            };

            /** @var {Object} dropzone */
            $(this.options.dropzone).dropzone(settings);
        }
    });

    return $.mage.simpleReturnsDragAndDrop;
});

/**
 * delete-attachment.js
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
define([
    'jquery',
    'mage/storage',
    'AuroraExtensions_SimpleReturns/js/url/builder',
    'AuroraExtensions_SimpleReturns/js/url/parser'
], function ($, storage, urlBuilder, urlParser) {
    'use strict';

    $.widget('mage.simpleReturnsDeleteAttachment', {
        options: {
            attachKey: null,
            container: '.attachments',
            routePath: 'simplereturns/rma_attachment/deletePost'
        },
        _create: function () {
            this.element.on('click', $.proxy(this.onClick, this));
        },
        onClick: function (clickEvent) {
            var attachKey, targetUrl, routePath;

            /** @var {String} attachKey */
            attachKey = this.options.attachKey;

            /** @var {String} routePath */
            routePath = this.options.routePath;

            /** @var {String} targetUrl */
            targetUrl = urlBuilder.getUrl(
                routePath,
                {
                    'rma_id': urlParser.getParamValue('rma_id'),
                    'token' : urlParser.getParamValue('token'),
                    'attach_key': attachKey
                }
            );

            return storage.post(targetUrl);
        }
    });

    return $.mage.simpleReturnsDeleteAttachment;
});

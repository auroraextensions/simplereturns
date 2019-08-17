/**
 * edit-status.js
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
    'AuroraExtensions_SimpleReturns/js/url/builder',
    'AuroraExtensions_SimpleReturns/js/url/parser',
    'AuroraExtensions_SimpleReturns/js/utils/dataTypes'
], function ($, urlBuilder, urlParser, dataTypes) {
    'use strict';

    var widget, urn;

    /** @var {Object} widget */
    widget = {
        /** @property {String} name */
        name: 'simpleReturnsRmaEditStatus',
        /** @property {String} container */
        container: 'mage',
        /** @property {Object} options */
        options: {},
        /**
         * @return {void}
         */
        _create: function () {
            this.element.on('click', $.proxy(this.onClick, this));
        },
        /**
         * @return {String}
         */
        getUrn: function () {
            return [this.container, this.name].join('.');
        },
        /**
         * @return {void}
         */
        onError: function () {
            /** @todo: Set error details via customer message. */
        },
        /**
         * @return {void}
         */
        onSuccess: function () {
            window.location.reload(true);
        },
        /**
         * @param {Object} clickEvent
         * @return {void}
         */
        onClick: function (clickEvent) {
            var actionUrl, data, settings, statusCode;

            /** @var {String|null} actionUrl */
            actionUrl = this.options.actionUrl;

            if (!actionUrl) {
                return null;
            }

            /** @var {String|null} statusCode */
            statusCode = this.options.statusCode;

            if (!statusCode) {
                return null;
            }

            /** @var {String} data */
            data = JSON.stringify({
                'status': statusCode
            });

            /** @var {Object} settings */
            settings = {
                beforeSend: function () {},
                contentType: 'application/json',
                data: data,
                error: this.onError.bind(this),
                method: 'POST',
                success: this.onSuccess.bind(this)
            };

            $.ajax(actionUrl, settings);
        }
    };

    /** @var {String} urn */
    urn = widget.getUrn
        .call(widget);

    $.widget(urn, widget);

    return $[widget.container][widget.name];
});

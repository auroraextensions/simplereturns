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

    /** @var {Object} widget */
    var widget = {
        /** @property {String} name */
        name: 'simpleReturnsDragAndDrop',
        /** @property {String} container */
        container: 'mage',
        /**
         * @property {Object} options
         */
        options: {
            attachKey: '',
            dropzone: '.dropzone',
            targetPath: '/simplereturns/rma_attachment/createPost/'
        },
        /**
         * @return {String}
         */
        getUrn: function () {
            return this.container + '.' + this.name;
        },
        /**
         * @return {void}
         */
        _create: function () {
            var onError, onFinish, targetPath;

            /** @var {String} targetPath */
            targetPath = this.options.targetPath;

            /** @var {Function} onError */
            onError = this.onError.bind(this);

            /** @var {Function} onFinish */
            onFinish = this.onFinish.bind(this);

            /* Prevent Dropzone from attaching twice. */
            Dropzone.autoDiscover = false;

            /* Extend Dropzone configuration. */
            Dropzone.options.attachmentDropzone = {
                url: targetPath,
                maxFilesize: 5,
                uploadMultiple: true,
                paramName: 'attachments',
                error: onError,
                success: onFinish
            };

            $(this.options.dropzone).dropzone(Dropzone.options.attachmentDropzone);
        },
        /**
         * @return {void}
         */
        onError: function () {
            /** @todo: Work on implementation. */
        },
        /**
         * @param {File} file
         * @param {String} data
         * @param {ProgressEvent} response
         * @return {void}
         */
        onFinish: function (file, data, response) {
            /** @todo: Work on implementation. */
        }
    };

    $.widget(
        widget.getUrn.call(widget),
        widget
    );

    return $[widget.container][widget.name];
});

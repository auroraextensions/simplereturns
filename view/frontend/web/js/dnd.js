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

    var widget, urn;

    /** @var {Object} widget */
    widget = {
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
            preload: false,
            preloadPath: '/simplereturns/rma_attachment/search/',
            targetPath: '/simplereturns/rma_attachment/createPost/',
            rmaId: null,
            token: null
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
                addRemoveLinks: true,
                error: onError,
                maxFilesize: 5,
                paramName: 'attachments',
                success: onFinish,
                uploadMultiple: true,
                url: targetPath
            };

            /* Preload existing images. Intended for edit page. */
            if (this.options.preload) {
                Dropzone.options.attachmentDropzone.init = this.preload.bind(this);
            }

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
        },
        /**
         * @return {void}
         */
        preload: function () {
            var callback, rmaId,
                token, url;

            /** @var {String} rmaId */
            rmaId = this.options.rmaId
                ? this.options.rmaId
                : urlParser.getParamValue('rma_id');

            /** @var {String} token */
            token = this.options.token
                ? this.options.token
                : urlParser.getParamValue('token');

            /** @var {String} url */
            url = urlBuilder.getUrl(
                this.options.preloadPath,
                {
                    'rma_id': rmaId,
                    'token': token
                }
            );

            /** @var {Function} callback */
            callback = this.onPreloadResponse
                .bind(Dropzone.options.attachmentDropzone);

            $.get(url, callback);
        },
        /**
         * @param {Object} data
         * @return {void}
         * @this {Dropzone.options.attachmentDropzone}
         */
        onPreloadResponse: function (data) {
            var self, file;

            data = data || false;

            if (!data) {
                return null;
            }

            /** @var {this} self */
            self = this;

            $.each(data, function (key, value) {
                /** @var {Object} file */
                file = {
                    name: value.name,
                    path: value.path,
                    size: value.size
                };

                self.emit('addedfile', file);
                self.options.thumbnail.call(self, file, file.path);
                self.emit('complete', file);
            });
        }
    };

    /** @var {String} urn */
    urn = widget.getUrn.call(widget);

    $.widget(urn, widget);

    return $[widget.container][widget.name];
});

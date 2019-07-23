<?php
/**
 * ModuleComponentInterface.php
 *
 * Module shared dictionary interface.
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
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Shared;

interface ModuleComponentInterface
{
    /** @constant string ADDRESS_FIELD_DELIMITER */
    const ADDRESS_FIELD_DELIMITER = ',';

    /** @constant string ATTRIBUTE_CODE_SIMPLE_RETURN */
    /** @constant string ATTRIBUTE_LABEL_SIMPLE_RETURN */
    const ATTRIBUTE_CODE_SIMPLE_RETURN = 'simple_return';
    const ATTRIBUTE_LABEL_SIMPLE_RETURN = 'Returnable';

    /** @constant string BLOCK_SIMPLERETURNS_LABEL_INDEX */
    /** @constant string BLOCK_SIMPLERETURNS_ORDERS_RESULTS */
    /** @constant string BLOCK_SIMPLERETURNS_ORDERS_SEARCH */
    const BLOCK_SIMPLERETURNS_LABEL_INDEX = 'simplereturns_label_index';
    const BLOCK_SIMPLERETURNS_ORDERS_RESULTS = 'simplereturns_orders_results';
    const BLOCK_SIMPLERETURNS_ORDERS_SEARCH = 'simplereturns_orders_search';

    /** @constant string COLUMN_HEADERS_KEY */
    const COLUMN_HEADERS_KEY = 'column_headers';

    /** @constant string DATA_PERSISTOR_KEY */
    const DATA_PERSISTOR_KEY = 'simplereturns_data';

    /** @constant string DEFAULT_FRONT_NAME */
    /** @constant string DEFAULT_SCRIPT_NAME */
    const DEFAULT_FRONT_NAME = 'simplereturns';
    const DEFAULT_SCRIPT_NAME = 'index.php';

    /** @constant string ERROR_DEFAULT_MESSAGE */
    /** @constant string ERROR_INVALID_CARRIER_CODE */
    /** @constant string ERROR_INVALID_EXCEPTION_TYPE */
    /** @constant string ERROR_INVALID_RETURN_LABEL_URL */
    /** @constant string ERROR_INVALID_TRAIT_CONTEXT */
    /** @constant string ERROR_MISSING_URL_PARAMS */
    /** @constant string ERROR_NO_SUCH_ENTITY_FOUND_FOR_EMAIL */
    /** @constant string ERROR_NO_SUCH_ENTITY_FOUND_FOR_ORDER_ID_ZIP_CODE */
    /** @constant string ERROR_ORDER_EXCEEDS_AGE_THRESHOLD */
    /** @constant string ERROR_ORDER_HAS_INELIGIBLE_ITEMS */
    /** @constant string ERROR_ORDER_SUBTOTAL_BELOW_MINIMUM */
    const ERROR_DEFAULT_MESSAGE = 'An error has occurred and we are unable to process the request.';
    const ERROR_INVALID_CARRIER_CODE = '%1 is not a valid carrier code.';
    const ERROR_INVALID_EXCEPTION_TYPE = 'Invalid exception class type %1 was given.';
    const ERROR_INVALID_RETURN_LABEL_URL = 'The requested return label URL was invalid. Please verify and try again.';
    const ERROR_INVALID_TRAIT_CONTEXT = '%1 can only be used when extending or implementing %2';
    const ERROR_MISSING_URL_PARAMS = 'Please provide an email address or order ID and billing/shipping zip code.';
    const ERROR_NO_SUCH_ENTITY_FOUND_FOR_EMAIL = 'Could not find any orders associated with email: %1';
    const ERROR_NO_SUCH_ENTITY_FOUND_FOR_ORDER_ID_ZIP_CODE = 'Could not find an order #%1 with billing or shipping zip code: %2';
    const ERROR_ORDER_EXCEEDS_AGE_THRESHOLD = 'Return labels are not available for orders more than %1 days old.';
    const ERROR_ORDER_HAS_INELIGIBLE_ITEMS = 'The selected order has items that do not permit online return label generation.<br><br>Please contact <a href="%1">%2</a> for assistance.';
    const ERROR_ORDER_SUBTOTAL_BELOW_MINIMUM = 'Return labels are not available for orders under %1';

    /** @constant string FIELD_CUSTOMER_ID */
    /** @constant string FIELD_INCREMENT_ID */
    /** @constant string FIELD_PROTECT_CODE */
    const FIELD_CUSTOMER_ID = 'customer_id';
    const FIELD_INCREMENT_ID = 'increment_id';
    const FIELD_PROTECT_CODE = 'protect_code';

    /** @constant string FORMAT_RMA_ORDER_REFERENCE */
    /** @constant string FORMAT_RMA_REQUEST_COMMENT */
    const FORMAT_RMA_ORDER_REFERENCE = 'RMA for Order #%1';
    const FORMAT_RMA_REQUEST_COMMENT = 'A return label was generated from [%1] with tracking number %2';

    /** @constant string FULLACTION_DELIMITER */
    /** @constant string FULLACTION_SIMPLERETURNS_LABEL_INDEX */
    const FULLACTION_DELIMITER = '_';
    const FULLACTION_SIMPLERETURNS_LABEL_INDEX = 'simplereturns_label_index';

    /** @constant string LABEL_CACHE_ID */
    const LABEL_CACHE_ID = 'SIMPLERETURNS_RETURN_LABEL_CONTENT_PER_ORDER';

    /** @constant string PARAM_EMAIL */
    /** @constant string PARAM_ORDER_ID */
    /** @constant string PARAM_PROTECT_CODE */
    /** @constant string PARAM_PKG_ID */
    /** @constant string PARAM_RMA_ID */
    /** @constant string PARAM_TOKEN */
    /** @constant string PARAM_ZIP_CODE */
    const PARAM_EMAIL = 'email';
    const PARAM_ORDER_ID = 'order_id';
    const PARAM_PROTECT_CODE = 'code';
    const PARAM_PKG_ID = 'pkg_id';
    const PARAM_RMA_ID = 'rma_id';
    const PARAM_TOKEN = 'token';
    const PARAM_ZIP_CODE = 'zip_code';

    /** @constant string PATH_INDEX_DELIMITER */
    const PATH_INDEX_DELIMITER = '/';

    /** @constant string PREFIX_DATAURI */
    const PREFIX_DATAURI = 'data:image/jpeg;base64,';

    /** @constant string ROUTE_SALES_GUEST_VIEW */
    /** @constant string ROUTE_SIMPLERETURNS_LABEL_INDEX */
    /** @constant string ROUTE_SIMPLERETURNS_PKG_CREATE */
    /** @constant string ROUTE_SIMPLERETURNS_PKG_CREATEPOST */
    /** @constant string ROUTE_SIMPLERETURNS_PKG_VIEW */
    /** @constant string ROUTE_SIMPLERETURNS_RMA_CREATE */
    /** @constant string ROUTE_SIMPLERETURNS_RMA_CREATEPOST */
    /** @constant string ROUTE_SIMPLERETURNS_RMA_VIEW */
    /** @constant string ROUTE_SIMPLERETURNS_ORDERS_RESULTS */
    /** @constant string ROUTE_SIMPLERETURNS_ORDERS_SEARCH */
    /** @constant string ROUTE_SIMPLERETURNS_ORDERS_SEARCHPOST */
    const ROUTE_SALES_GUEST_VIEW = 'sales/guest/view';
    const ROUTE_SIMPLERETURNS_LABEL_INDEX = 'simplereturns/label/index';
    const ROUTE_SIMPLERETURNS_PKG_CREATE = 'simplereturns/package/create';
    const ROUTE_SIMPLERETURNS_PKG_CREATEPOST = 'simplereturns/package/createPost';
    const ROUTE_SIMPLERETURNS_PKG_VIEW = 'simplereturns/package/view';
    const ROUTE_SIMPLERETURNS_RMA_CREATE = 'simplereturns/rma/create';
    const ROUTE_SIMPLERETURNS_RMA_CREATEPOST = 'simplereturns/rma/createPost';
    const ROUTE_SIMPLERETURNS_RMA_VIEW = 'simplereturns/rma/view';
    const ROUTE_SIMPLERETURNS_ORDERS_RESULTS = 'simplereturns/orders/results';
    const ROUTE_SIMPLERETURNS_ORDERS_SEARCH = 'simplereturns/orders/search';
    const ROUTE_SIMPLERETURNS_ORDERS_SEARCHPOST = 'simplereturns/orders/searchPost';

    /** @constant string SQL_COLUMN_LABEL_PRIMARY_FIELD */
    /** @constant string SQL_COLUMN_PKG_PRIMARY_FIELD */
    /** @constant string SQL_COLUMN_RMA_PRIMARY_FIELD */
    /** @constant string SQL_COLUMN_RMA_ORDER_ID_FIELD */
    /** @constant string SQL_TABLE_ENTITY_LABEL */
    /** @constant string SQL_TABLE_ENTITY_PKG */
    /** @constant string SQL_TABLE_ENTITY_RMA */
    const SQL_COLUMN_LABEL_PRIMARY_FIELD = 'label_id';
    const SQL_COLUMN_PKG_PRIMARY_FIELD = 'package_id';
    const SQL_COLUMN_RMA_PRIMARY_FIELD = 'rma_id';
    const SQL_COLUMN_RMA_ORDER_ID_FIELD = 'order_id';
    const SQL_TABLE_ENTITY_LABEL = 'simplereturns_label';
    const SQL_TABLE_ENTITY_PKG = 'simplereturns_package';
    const SQL_TABLE_ENTITY_RMA = 'simplereturns_rma';

    /** @constant string XML_LAYOUT_HANDLE_NOROUTE */
    const XML_LAYOUT_HANDLE_NOROUTE = 'simplereturns_noroute';

    /** @constant int ZIP_CODE_INDEX */
    /** @constant int ZIP_CODE_LENGTH */
    const ZIP_CODE_INDEX = 0;
    const ZIP_CODE_LENGTH = 5;
}

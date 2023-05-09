<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 23:55:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

enum TabsAbbreviationEnum: string
{
    case WEBSITES          = 'webs';
    case STATS             = 'sts';
    case WAREHOUSE_AREAS   = 'wa';
    case LOCATIONS         = 'loc';
    case PAYMENT_ACCOUNTS  = 'pa';
    case PAYMENTS          = 'p';
    case PORTFOLIO         = 'po';
    case PRODUCTS          = 'prod';
    case ORDERS            = 'o';
    case SALES             = 'sales';
    case INSIGHTS          = 'i';
    case DISCOUNTS         = 'disc';
    case CREDITS           = 'cred';
    case ATTACHMENTS       = 'attach';
    case DISPATCHED_EMAILS = 'de';
    case DATA              = 'data';
    case DASHBOARD         = 'dash';
    case CHANGELOG         = 'hist';
    case CUSTOMERS         = 'cus';
    case DEPARTMENTS       = 'dep';
    case PURCHASES_SALES   = 'ps';
    case SUPPLIER_PRODUCTS = 'sp';

    case WAREHOUSE = 'w';

    case SUPPLIER_DELIVERIES = 'spd';

    case OUTBOXES        = 'out';
    case ISSUES          = 'iss';
    case PURCHASE_ORDERS = 'puord';
    case DELIVERIES      = 'd';
    case IMAGES          = 'img';

    case AGENTS                             = 'ag';
    case SYSTEM_USERS                       = 'syuser';
    case MARKETPLACE_AGENTS                 = 'mpa';
    case MARKETPLACE_SUPPLIERS              = 'mps';
    case PROSPECTS                          = 'pro';
    case EMPLOYEES                          = 'emp';
    case ITEMS                              = 'itms';
    case CUSTOMER_NOTES_HISTORY             = 'cnh';
    case SENT_EMAILS                        = 'se';
    case INVOICES                           = 'inv';
    case DELIVERY_NOTES                     = 'dn';
    case SKOS_ORDERED                       = 'skoord';
    case UNITS                              = 'u';
    case TARIFF_CODES_ORIGIN                = 'tco';
    case PROPERTIES_OPERATIONS              = 'propt';
    case SUPPLIERS                          = 'su';
    case STOCK_HISTORY                      = 'sh';
    case PARTS                              = 'parts';
    case DISCONTINUED_PARTS                 = 'dp';
    case PRODUCT_FAMILIES                   = 'pf';
    case STOCK_MOVEMENTS                    = 'sm';
    case STOCK_FAMILIES                     = 'sf';

    case FAMILIES                           = 'fam';
    case ALL_PRODUCTS                       = 'allp';
    case VOUCHERS                           = 'v';
    case SETTINGS                           = 's';
    case VARIATIONS                         = 'vt';
    case WEBPAGES                           = 'web';
    case MAILSHOTS                          = 'ms';
    case RELATED_PRODUCTS                   = 'rp';
    case COMMUNICATIONS_HISTORY_NOTES       = 'chn';
    case SUBCATEGORIES                      = 'subc';
    case OFFERS                             = 'off';
    case RELATED_CATEGORIES                 = 'rc';
    case NOTIFICATIONS_TO_BE_SEND_NEXT_SHOT = 'ntbsns';
    case WORKSHOP                           = 'ws';
    case POOL_OPTIONS                       = 'popt';
    case HEADER                             = 'hd';
    case MENU                               = 'mn';
    case FOOTER                             = 'ft';
    case NAME                               = 'n';
    case EMAIL                              = 'em';
    case PHONE                              = 'ph';
    case IDENTITY_DOCUMENT_NUMBER           = 'idn';
    case WORKER_NUMBER                      = 'wn';
    case JOB_TITLE                          = 'jt';
    case EMERGENCY_CONTACT                  = 'ec';
    case GENDER                             = 'gd';
    case DATE_OF_BIRTH                      = 'dob';

    case USERNAME                           = 'us';
    case ABOUT                              = 'ab';
    case REMEMBER_TOKEN                     = 'rt';
    case PASSWORD                           = 'pw';

    case GUEST                              = 'gue';
    case USER                               = 'usr';

    case SHOPS                              = 'shps';

    case ANALYTICS                          = 'ana';

    case USERS                              = 'usrs';
}

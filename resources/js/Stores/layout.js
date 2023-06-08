/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

import {defineStore} from 'pinia';
import {trans} from 'laravel-vue-i18n';

export const useLayoutStore = defineStore('layout', {
    state: () => (
        {
            navigation : [],
            shopsInDropDown: {},
            shops      : {},
            currentShopSlug: null,
            currentShopData: {
                slug: null,
                name: trans('All shops'),
                code: trans('All')
            },
            websites: {
                routeSingle: 'websites.show',
                routeAll: 'websites.index',
                labelShowAll: trans('All websites'),
                currentData: {
                    slug: null,
                    name: trans('All websites'),
                    code: trans('All')
                }
            },
            crm: {
                routeSingle: 'crm.shop.dashboard',
                routeAll: 'crm.dashboard',
                labelShowAll: trans('All websites'),
                currentData: {
                    slug: null,
                    name: trans('All websites'),
                    code: trans('All')
                }
            },
            customers: {
                routeSingle: 'inventory.warehouses.show',
                routeAll: 'inventory.warehouses.index',
                labelShowAll: trans('All Customers'),
                currentData: {
                    slug: null,
                    name: trans('All customers'),
                    code: trans('All')
                }
            },
            inventory: {
                routeSingle: 'inventory.warehouses.show',
                routeAll: 'inventory.warehouses.index',
                labelShowAll: trans('All warehouses'),
                currentData: {
                    slug: null,
                    name: trans('All warehouses'),
                    code: trans('All')
                }
            },
            tenant     : {},
        }
    ),

});


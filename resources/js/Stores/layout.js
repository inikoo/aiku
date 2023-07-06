/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

import { defineStore } from "pinia";
import { trans } from "laravel-vue-i18n";

export const useLayoutStore = defineStore("layout", {
    state: () => (
        {
            booted:  false,
            navigation            : [],
            shopsInDropDown       : {},
            shops                 : {},
            currentShopSlug       : null,
            currentShopData       : {
                slug: null,
                name: trans("All shops"),
                code: trans("All")
            },
            websitesInDropDown       : {},
            websites                 : {},
            currentWebsiteSlug       : null,
            currentWebsiteData       : {
                slug: null,
                name: trans("All websites"),
                code: trans("All")
            },
            warehousesInDropDown       : {},
            warehouses                 : {},
            currentWarehouseSlug       : null,
            currentWarehouseData       : {
                slug: null,
                name: trans("All warehouses"),
                code: trans("All")
            },
            tenant                : {},
            currentRoute          : "",
            currentRouteParameters: {},
            currentModule         : ""
        }
    )

});


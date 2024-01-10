/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */
import { grpNavigation, orgNavigation } from "@/types/Navigation"


import { defineStore } from "pinia";
import { trans } from "laravel-vue-i18n";
import { Image } from "@/types/Image"

export const useLayoutStore = defineStore("layout", {
    state: () => (
        {
            avatar_thumbnail    : {
                original   : "",
                original_2x: "",
                avif       : "",
                avif_2x    : "",
                webp       : "",
                webp_2x    : ""
            },
            booted              : false,
            groupNavigation     : [],
            orgNavigation       : [],
            shopsInDropDown     : {},
            shops               : {},
            currentShopSlug     : null,
            currentShopData     : {
                slug: null,
                name: trans("All shops"),
                code: trans("All")
            },
            websitesInDropDown  : {},
            websites            : {},
            currentWebsiteSlug  : null,
            currentWebsiteData  : {
                slug: null,
                name: trans("All websites"),
                code: trans("All")
            },
            // currentWarehouseSlug: null,
            currentWarehouseData: {
                slug: null,
                name: trans("All warehouses"),
                code: trans("All")
            },
            group: {
                logo: {} as Image,
                slug: "",
                name: ""
            },
            leftSidebar: {
                show: true,
            },
            navigation: {
                grp: {} as grpNavigation,
                org: {} as {[key: string]: orgNavigation}
            },
            organisations       : {
                currentOrganisations: '',
                data: {} as { slug: string, logo: Image, name: string}[]
            },
            currentRoute          : "",
            currentRouteParameters: {},
            currentModule         : "",
            rightSidebar          : {
                activeUsers: {
                    users: [],
                    count: 0,
                    show : false
                },
                language   : {
                    show: false
                }
            },
            systemName            : "",  // For styling navigation depend on which App
            user                  : {
                username        : "",
                name            : "",
                avatar_thumbnail: null,
                customer_slug   : null,
                customer_name   : null
            },
            warehousesInDropDown: {},
            warehouses          : {},
        }
    )

});


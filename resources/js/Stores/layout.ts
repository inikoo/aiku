/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */
import { grpNavigation, orgNavigation } from "@/types/Navigation"


import { defineStore } from "pinia";
import { trans } from "laravel-vue-i18n";
import { Image } from "@/types/Image"

interface OrganisationsData {
    authorised_shops: {
        slug: string
        code: string
        name: string
    }[]
    authorised_warehouses: {
        slug: string
        code: string
        name: string
    }
    code: string
    name: string
    slug: string
    logo: Image
}

interface Group {
    logo: Image
    slug: string
    name: string
}

export const useLayoutStore = defineStore("layout", {
    state: () => (
        {
            booted              : false,
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
            group: {} as Group,
            leftSidebar: {
                show: true,
            },
            navigation: {
                grp: {} as grpNavigation,
                org: {} as {[key: string]: orgNavigation}
            },
            organisations: {
                // currentOrganisations: '',
                data: {} as OrganisationsData[]
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
            systemName: "",  // For styling navigation depend on which App
            user: {} as { avatar_thumbnail: Image, email: string, username: string },
            warehousesInDropDown: {},
            warehouses          : {},
        }
    )

});


/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */
import { grpNavigation, orgNavigation } from "@/types/Navigation"


import { defineStore } from "pinia"
import { trans } from "laravel-vue-i18n"
import { Image } from "@/types/Image"
import { routeType } from "@/types/route"

interface OrganisationsData {
    id: number
    slug: string
    code: string
    name: string
    logo: Image
    route: routeType
    authorised_shops: {
        id: number
        slug: string
        code: string
        name: string
        state: string
        type: string
        route: routeType
    }[]
    authorised_warehouses: {
        id: number
        slug: string
        code: string
        name: string
        route: routeType
    }
}

interface Group {
    logo: Image
    slug: string
    name: string
}

export const useLayoutStore = defineStore("layout", {
    state: () => (
        {
            booted: false,
            currentModule: "",
            currentRoute: "grp.dashboard.show", // Define value to avoid route null at first load
            currentParams: {} as {[key: string]: string},
            group: {} as Group,
            leftSidebar: {
                show: true,
            },
            navigation: {
                grp: {} as grpNavigation,
                org: {} as { [key: string]: orgNavigation }
            },
            organisations: {
                // currentOrganisations: '',
                data: {} as OrganisationsData[]
            },
            rightSidebar: {
                activeUsers: {
                    users: [],
                    count: 0,
                    show: false
                },
                language: {
                    show: false
                }
            },
            systemName: "",  // For styling navigation depend on which App
            user: {} as { avatar_thumbnail: Image, email: string, username: string },
        }
    )
});


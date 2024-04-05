/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */
import { Navigation, grpNavigation, orgNavigation } from "@/types/Navigation"
import { useColorTheme } from '@/Composables/useStockList'


import { defineStore } from "pinia"
import { Image } from "@/types/Image"
import { routeType } from "@/types/route"
import { Colors } from "@/types/Color"

interface OrganisationsData {
    id: number
    slug: string
    code: string
    label: string
    logo: Image
    route: routeType
    authorised_shops: {
        id: number
        slug: string
        code: string
        label: string
        state: string
        type: string
        route: routeType
    }[]
    authorised_warehouses: {
        id: number
        slug: string
        code: string
        label: string
        state: string
        route: routeType
    }[]
    authorised_fulfilments: {
        id: number
        slug: string
        code: string
        label: string
        state: string
        type: string
        route: routeType
    }[]
}

interface Group {
    logo: Image
    slug: string
    label: string
}

// Each organisation have their own state
interface OrganisationState {
    [key: string] : string  // 'currentShop' | 'currentWarehouse' | 'currentFulfilment'
}

export const useLayoutStore = defineStore("layout", {
    state: () => (
        {
            agents: null,
            app: {
                name: "",  // For styling navigation depend on which App
                color: null as unknown | Colors,  // Styling layout color
                theme: useColorTheme[0] as string[],  // For styling app color
                url: null as string | null, // For url on logo top left
            },
            currentModule: "",
            currentRoute: "grp.dashboard.show", // Define value to avoid route null at first load
            currentParams: {} as {[key: string]: string},
            group: null as Group | null,
            leftSidebar: {
                show: true,
            },
            navigation: {
                grp: {} as grpNavigation,
                org: {} as { [key: string]: orgNavigation } | { [key: string]: Navigation } | Navigation
            },
            organisations: {
                // currentOrganisations: '',
                data: {} as OrganisationsData[]
            },
            organisationsState: {} as {[key: string]: OrganisationState},
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
            user: {} as { id: number, avatar_thumbnail: Image, email: string, username: string },
        }
    ),

    getters: {
        isShopPage: (state) => {
            return (state.currentRoute).includes('grp.org.shops.')
        },
        isFulfilmentPage: (state) => {
            return (state.currentRoute).includes('grp.org.fulfilments.')
        },
        // liveUsersWithoutMe: (state) => state.liveUsers.filter((liveUser, keyUser) => keyUser != layout.user.id )
    },
});


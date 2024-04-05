
import { Image } from "@/types/Image"
import { routeType } from "@/types/route"

export interface OrganisationsData {
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

export interface Group {
    logo: Image
    slug: string
    label: string
}

// Each organisation have their own state
export interface OrganisationState {
    [key: string] : string  // 'currentShop' | 'currentWarehouse' | 'currentFulfilment'
}
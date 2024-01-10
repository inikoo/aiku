import { routeType } from "@/types/route"

export interface DropdownLink {
    label?: string
    tooltip: string
    icon: string[]
    route: {
        all: string[]
        selected: string[]
    }
}

export interface SubSection {
    label: string
    icon: string | string[]
    route: routeType
}

export interface Navigation {
    label: string
    scope?: string
    icon: string[]
    route?: routeType
    topMenu: {
        subSections?: SubSection[]
        dropdown?: {
            links: DropdownLink[]
        }
    }
}

export interface grpNavigation {
    [key: string]: Navigation
}

export interface orgNavigation {
    [orgKey: string]: {
        [shopKey: string]: { [navShopKey: string]: Navigation };
    };
}
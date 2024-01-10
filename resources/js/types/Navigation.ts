import { routeType } from "@/types/route"

export interface navigation {
    label: string
    scope?: string
    icon: string[]
    route: routeType
    topMenu: {
        subSections?: {
            label: string
            icon: string[]
            route: routeType
        }[]
        dropdown?: {
            links: {
                label?: string
                tooltip: string
                icon: string[]
                route: {
                    all: string[]
                    selected: string[]
                }
            }[]
        }
    }
}

export interface grpNavigation {
    [key: string]: navigation
}

export interface orgNavigation {
    [key: string]: {
        [key: string]: navigation | { [key: string]: { [key: string]: navigation } }
    }
}
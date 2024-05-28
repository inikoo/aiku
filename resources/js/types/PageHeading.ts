import { Button } from '@/types/Button'
import { routeType } from '@/types/route'
import { Action } from './Action'

export interface PageHeading {
    actions: Action[]
    actionActualMethod?: string
    after_title?: {
        label: string
        class?: string
    }
    container: {
        icon: string | string[]
        label: string
        href?: routeType
        tooltip: string
    }
    edit: {
        route: routeType
    }
    noCapitalise?: boolean  // Off capitalize in 'title'
    meta?: any
    model: string  // Define the type page ('Pallet Delivery' or 'Pallet Returns', etc)
    icon: {
        icon: string | string[]
        title: string
        tooltip?: string
    }
    iconRight?: {
        tooltip: string
        icon: string
        class: string
    }
    title: string
    subNavigation?: any
}
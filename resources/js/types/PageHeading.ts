import { Button } from '@/types/Button'
import { routeType } from '@/types/route'

export interface PageHeading {
    actions: {
        type: string
        buttons: Button[]
    }[]
    container: {
        icon: string | string[]
        label: string
        tooltip: string
    }
    edit: {
        route: routeType
    }
    icon: {
        icon: string | string[]
        title: string
    }
    iconRight?: {
        tooltip: string
        icon: string
        class: string
    }
    title: string
}
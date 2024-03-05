import { routeType } from '@/types/route'

export interface Button {
    style: string
    icon?: string | string[]
    label?: string
    route: routeType
}

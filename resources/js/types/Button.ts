import { routeType } from '@/types/route'

export interface Button {
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
    style?: string
    icon?: string | string[]
    iconRight?: string | string[]
    disabled?: boolean
    label?: string
    tooltip?: string
    route: routeType
    target?: string  // '_blank'
}

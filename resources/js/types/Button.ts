import { routeType } from '@/types/route'

export interface Button {
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
    style?: string
    icon?: string | string[]
    label?: string
    tooltip?: string
    route: routeType
    target?: string  // '_blank'
}

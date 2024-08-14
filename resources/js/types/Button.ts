import { routeType } from '@/types/route'

export interface Button {
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
    key?: string
    style?: string
    icon?: string | string[]
    iconRight?: string | string[]
    disabled?: boolean
    label?: string
    size?: string
    tooltip?: string
    route: routeType
    target?: string  // '_blank'
    fullLoading?: boolean   // true: make the loading endless
}

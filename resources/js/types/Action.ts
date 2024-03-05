import { routeType } from '@/types/route'
import { Button } from '@/types/Button'

export interface Action {
    key: string
    type?: string  // undefined (button) | button | buttonGroup
    icon?: string | string[]
    label?: string
    iconRight?: string | string[]
    style?: string
    route?: routeType
    tooltip?: string
    button?: Button[]
    
    buttonGroup?: {
        // If type = buttonGroup
        icon?: string | string[]
        label?: string
        iconRight?: string | string[]
        style?: string
        route?: routeType
        tooltip?: string
    }[]
}
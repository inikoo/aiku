import { routeType } from '@/types/route'
import { Button } from '@/types/Button'

export interface Action {
    key: string
    type?: string  // undefined (button) | button | buttonGroup
    icon?: string | string[]
    label?: string
    iconRight?: string | string[]
    style?: string
    size?: string
    route?: routeType
    href?: routeType  // Safety for old code, should be in 'route' 
    tooltip?: string
    target?: string  // '_blank'
    disabled?: boolean  // To open modal purpose in page heading (Fulfilment - Customer - Add delivery)
    fullLoading?: boolean   // true: make the loading endless
    
    button?: Button[]  // The button will separate
    buttonGroup?: Button[]  // The button will in same group
}
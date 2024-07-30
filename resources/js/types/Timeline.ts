export interface Timeline {
    key?: string
    index?: number
    label: string
    icon?: string | string[]
    tooltip?: string
    timestamp?: string | Date
    current?: boolean
}
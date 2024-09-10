export interface BoxNote {
    label: string
    note: string
    editable?: boolean
    bgColor?: string
    textColor?: string
    color?: string
    lockMessage?: string
    field: string  // customer_notes, public_notes, internal_notes
}

export interface Icon {
    icon: string | string[]
    tooltip?: string
    class?: string
}
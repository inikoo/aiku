import { Timeline } from "@/types/Timeline"

export interface PalletCustomer {
    slug: string
    reference: string
    name: string
    contact_name: string
    company_name?: string
    email?: string
    phone?: string
    created_at: Date
    updated_at: Date
    shop?: string
    shop_slug?: string
    number_active_clients?: number
}

export interface PieCustomer {
    label: string
    count: number
    cases: {
        value: string
        count: number
        label: string
        icon: {
            icon: string
            tooltip: string
            class: string
        }
    }[]
}

export interface PalletDelivery {
    id: number
    number_pallets: number
    reference: string
    state: string
    timeline: Timeline[]
}
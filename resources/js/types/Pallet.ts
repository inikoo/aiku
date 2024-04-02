import { Timeline } from "@/types/Timeline"
import { Customer } from '@/types/customer'

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
    customer_name: string
    number_pallets: number
    reference: string
    state: string
    timeline: Timeline[]
}

// Box Stats in Pallet Delivery
export interface PDBoxStats {
    fulfilment_customer: Customer
    delivery_status: PalletStatus
}

export interface PalletStatus {
    tooltip: string
    icon: string
    class: string
}
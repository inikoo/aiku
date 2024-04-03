import { Timeline } from "@/types/Timeline"
import { FulfilmentCustomer } from '@/types/Customer'

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
    delivery_status: PalletStatus
    fulfilment_customer: {
        customer: {
            slug: string
            reference: string
            name: string
            contact_name: string
            company_name: string
            location: string[]
            email: string
            phone: string
            created_at: string
        }
        fulfilment: {
            name: string
            slug: string
        }
        number_pallet_deliveries: number
        number_pallet_returns: number
        number_pallets: number
        number_pallets_state_received: number
        number_stored_items: number
        radioTabs: {
            dropshipping: boolean
            items_storage: boolean
            pallets_storage: boolean
        }
    }
}

export interface PalletStatus {
    tooltip: string
    icon: string
    class: string
}
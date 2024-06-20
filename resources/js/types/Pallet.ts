// PDR = Pallet Delivery/Return

import { Timeline } from "@/types/Timeline"
import { routeType } from "@/types/route"
import { Address, AddressValue } from "@/types/PureComponent/Address"



export interface Pallet {
    id: number
    slug?: string
    reference: string
    customer_reference: string
    fulfilment_customer_name?: string
    fulfilment_customer_slug?: string
    fulfilment_customer_id: number
    notes: string
    state: string
    state_label: string
    state_icon: {
        tooltip: string
        icon: string
        class: string
        color: string
        app: {
            name: string
            type: string
        }
    }
    status: string
    status_label: string
    status_icon: {
        tooltip: string
        icon: string
        class: string
        color: string
        app: {
            name: string
            type: string
        }
    }
    location: string
    location_code: string
    location_slug: string
    location_id: number
    stored_items: any[]
    stored_items_quantity: number
    updateRoute: routeType
    deleteRoute: routeType
    deleteFromDeliveryRoute: routeType
    deleteFromReturnRoute: routeType
    notReceivedRoute: routeType
    undoNotReceivedRoute: routeType
    bookInRoute: routeType
    undoBookInRoute: routeType
    updateLocationRoute: routeType
    storeStoredItemRoute: routeType
    index: number
    editingIndicator: {
        loading: boolean
        isSucces: boolean
        isFailed: boolean
    }
}

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
    number_current_clients?: number
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
    number_services: number
    number_physical_goods: number
    reference: string
    state: string
    timeline: Timeline[]
    estimated_delivery_date: string
}

export interface PalletReturn {
    id: number
    delivery_address: {
        formatted_address?: string
    }
    reference: string
    state: string
    timeline: {
        [key: string]: Timeline
    }
    number_pallets: number
    number_stored_items: number
    number_services: number
    number_physical_goods: number
}

// Box Stats in Pallet Delivery
export interface BoxStats {
    delivery_status: PalletStatus
    fulfilment_customer: {
        address: Address
        addresses_list: AddressValue[]
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
        slug: string
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
    order_summary: {
        number_pallets: number
        number_services: number
        number_physical_goods: number
        pallets_price: number
        physical_goods_price: number
        services_price: number
        total_pallets_price: number
        total_services_price: number
        total_physical_goods_price: number
        total_price: number
    }
}

export interface PalletStatus {
    tooltip: string
    icon: string
    class: string
}

// Pallet Delivery and Return notes
export interface PDRNotes {
    label: string
    note: string
    editable?: boolean
    bgColor?: string
    color?: string
    lockMessage?: string
    field: string  // customer_notes, public_notes, internal_notes
}

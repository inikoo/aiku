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
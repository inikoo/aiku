export interface Calculation {
    profit_amount: number
    margin_percentage: number
    charges: number
    currency: string
    discounts_total: number
    insurance: number
    item_gross: number
    items_net: number
    net_amount: number
    number: string
    payment_amount: number
    shipping: number
    tax_amount: number
    tax_percentage: number
    total_amount: number
}

export interface ProductTransaction {
    code: string
    name: string
    quantity: string
    description?: string
    price: string
}
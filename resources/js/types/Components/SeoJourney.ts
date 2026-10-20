import { routeType } from "@/types/route"

export interface JourneySource {
    type: string | null
    label: string
    reference: string | null
}

export interface JourneyOrder {
    purchase_id: number
    reference: string
    submitted_at: string
    net_amount: number
    currency_code: string | null
    customer: { name: string | null, route: routeType }
    order_route: routeType
    visits: number
    sources: JourneySource[]
}

export interface JourneyFilters {
    sources: string[]
    visits: string | null
    min: number | null
    max: number | null
    landing: string | null
    search: string | null
}

export interface JourneyData {
    days: number
    filters: JourneyFilters
    total: number
    matching: number
    page: number
    per_page: number
    currency_code: string | null
    source_options: { value: string, label: string }[]
    orders: JourneyOrder[]
}

export interface JourneyPage {
    path: string
    type: string | null
    viewed_at: string
}

export interface JourneyVisit {
    id: number
    started_at: string
    ended_at: string
    source: JourneySource
    landing_path: string | null
    pages: JourneyPage[]
}

export interface OrderJourney extends Omit<JourneyOrder, "visits" | "sources"> {
    visits: JourneyVisit[]
}

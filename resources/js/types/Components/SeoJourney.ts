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

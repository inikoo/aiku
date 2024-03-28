export interface Links {
    first: string
    last: string
    prev?: string
    next?: string
}

export interface Meta {
    current_page: number
    from: number
    last_page: number
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    path: string
    per_page: number
    to: number
    total: number
}

export interface Table {
    data: any[]
    meta: Meta
    links: Links
}
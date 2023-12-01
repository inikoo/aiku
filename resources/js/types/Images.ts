export interface Images {
    created_at: string
    id: number
    mime_type: string
    name: string
    size: string
    slug: string
    source: {
        original: string
        webp: string
    }
    thumbnail?: {
        original?: string
        original_2x?: string
        webp?: string
        webp_2x?: string
    }
    was_recently_created: boolean
}
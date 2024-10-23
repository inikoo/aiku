export interface Image {
    original: string
    original_2x?: string
    avif?: string
    avif_2x?: string
    webp?: string
    webp_2x?: string
}

export interface ImageData {
    source: Image
    thumbnail: Image
    id: number
    name: string
}
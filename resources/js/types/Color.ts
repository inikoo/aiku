export interface RGBA {
    r: number
    g: number
    b: number
    a: number
}

export interface HSV {
    h: number
    s: number
    v: number
}

export interface Colors {
    rgba: RGBA
    hsv: HSV
    hex: string
}
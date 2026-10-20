/*
 * Author Louis Perez
 * Created on 17-10-2026-13h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

import { reactive } from "vue"

export type FaceKey = "front" | "back" | "left" | "right" | "top" | "bottom" | "sheet"

export const FACE_KEYS: FaceKey[] = ["front", "back", "left", "right", "top", "bottom"]

export type ElementType = "text" | "image" | "zone" | "data" | "pictogram" | "barcode" | "shape"

export interface DesignElement {
    id: string
    type: ElementType
    x: number
    y: number
    w: number
    h: number
    rotate?: number | null
    text?: string | null
    font_size?: number | null
    font_weight?: "normal" | "bold" | null
    italic?: boolean | null
    align?: "left" | "center" | "right" | null
    color?: string | null
    font_family?: string | null
    image_file_id?: number | null
    zone?: "compliance" | "barcode" | "other" | null
    label?: string | null
    source?: string | null
    group_id?: string | null
    locked?: boolean | null
    shape?: "rect" | "rounded" | "ellipse" | "triangle" | "line" | null
    fill?: string | null
    stroke?: string | null
    stroke_px?: number | null
    radius?: number | null
}

export interface Bounds {
    x: number
    y: number
    w: number
    h: number
}

/** the upright box around items, taking their rotation into account */
export function boundsOf(elements: DesignElement[]): Bounds | null {
    if (!elements.length) {
        return null
    }
    let minX = Infinity
    let minY = Infinity
    let maxX = -Infinity
    let maxY = -Infinity
    for (const element of elements) {
        const radians = ((element.rotate ?? 0) * Math.PI) / 180
        const cos = Math.cos(radians)
        const sin = Math.sin(radians)
        const centreX = element.x + element.w / 2
        const centreY = element.y + element.h / 2
        for (const [cornerX, cornerY] of [[-1, -1], [1, -1], [1, 1], [-1, 1]]) {
            const localX = (cornerX * element.w) / 2
            const localY = (cornerY * element.h) / 2
            const x = centreX + localX * cos - localY * sin
            const y = centreY + localX * sin + localY * cos
            minX = Math.min(minX, x)
            minY = Math.min(minY, y)
            maxX = Math.max(maxX, x)
            maxY = Math.max(maxY, y)
        }
    }
    return { x: minX, y: minY, w: maxX - minX, h: maxY - minY }
}

export interface DesignDataSources {
    text: { key: string, label: string }[]
    pictograms: { key: string, label: string, icon: string }[]
    barcodes: { key: string, label: string }[]
}

/** what the SKU's trade unit holds for each data source; absent on family-level rounds */
export interface DesignDataValues {
    text: Record<string, string | null>
    pictograms: Record<string, boolean>
    barcodes: Record<string, string | null>
}

export interface DesignData {
    sources: DesignDataSources
    values: DesignDataValues | null
}

export const COMPLIANCE_PARTS = ["warnings", "manufacturer", "eu_responsible", "ufi", "un_number"]

/** the parts a compliance area shows, kept in its `source` as a comma list; all of them when unset */
export function complianceParts(source: string | null | undefined): string[] {
    return source ? source.split(",").filter((part) => COMPLIANCE_PARTS.includes(part)) : COMPLIANCE_PARTS
}

/** the compliance block printed in a reserved compliance area */
export function complianceText(values: DesignDataValues | null, source?: string | null): string | null {
    if (!values) {
        return null
    }
    const lines = complianceParts(source).map((key) => values.text[key]).filter(Boolean)
    return lines.length ? lines.join("\n\n") : null
}

export interface DesignFace {
    background: { color: string | null, image_file_id: number | null, x?: number, y?: number, zoom?: number }
    elements: DesignElement[]
    groups?: Record<string, string>
}

export interface DesignLayout {
    kind?: "box" | "sheet"
    units: "mm" | "cm"
    width: number | null
    length: number | null
    height: number | null
    faces: Partial<Record<FaceKey, DesignFace>> & Record<string, DesignFace>
}

export interface DesignImage {
    id: number
    url: string | null
    name: string | null
}

export interface NetPanel {
    key: string
    face: FaceKey | null
    x: number
    y: number
    w: number
    h: number
}

export interface BoxNet {
    width: number
    height: number
    panels: NetPanel[]
}

/** 1 point in millimetres, for font sizes typed in pt */
export const PT_TO_MM = 0.3528

/**
 * A reverse tuck end carton laid flat, in millimetres: left side, front, right side and back in a row
 * with a glue flap on the back, the top and bottom folding off the front with their tuck flaps, and
 * dust flaps on the sides. Only the six faces carry artwork; flaps are drawn for shape.
 */
export function buildBoxNet(width: number, length: number, height: number): BoxNet {
    const tuck = Math.min(Math.max(length * 0.6, 8), 25)
    const dust = Math.min(length * 0.5, 30)
    const glue = Math.min(Math.max(length * 0.35, 8), 15)

    const top = tuck + length
    const rowY = top
    const xs = { left: 0, front: length, right: length + width, back: 2 * length + width }

    const panels: NetPanel[] = [
        { key: "left", face: "left", x: xs.left, y: rowY, w: length, h: height },
        { key: "front", face: "front", x: xs.front, y: rowY, w: width, h: height },
        { key: "right", face: "right", x: xs.right, y: rowY, w: length, h: height },
        { key: "back", face: "back", x: xs.back, y: rowY, w: width, h: height },
        { key: "glue", face: null, x: xs.back + width, y: rowY + height * 0.05, w: glue, h: height * 0.9 },
        { key: "top", face: "top", x: xs.front, y: tuck, w: width, h: length },
        { key: "top_tuck", face: null, x: xs.front, y: 0, w: width, h: tuck },
        { key: "bottom", face: "bottom", x: xs.front, y: rowY + height, w: width, h: length },
        { key: "bottom_tuck", face: null, x: xs.front, y: rowY + height + length, w: width, h: tuck },
        { key: "left_dust_top", face: null, x: xs.left, y: rowY - dust, w: length, h: dust },
        { key: "left_dust_bottom", face: null, x: xs.left, y: rowY + height, w: length, h: dust },
        { key: "right_dust_top", face: null, x: xs.right, y: rowY - dust, w: length, h: dust },
        { key: "right_dust_bottom", face: null, x: xs.right, y: rowY + height, w: length, h: dust },
    ]

    return {
        width: 2 * length + 2 * width + glue,
        height: tuck * 2 + length * 2 + height,
        panels,
    }
}

export function faceSize(layout: Pick<DesignLayout, "width" | "length" | "height">, face: FaceKey): { w: number, h: number } {
    if (face === "sheet") {
        return { w: layout.width ?? 0, h: layout.height ?? 0 }
    }
    const width = layout.width ?? 0
    const length = layout.length ?? 0
    const height = layout.height ?? 0

    switch (face) {
        case "front":
        case "back":
            return { w: width, h: height }
        case "left":
        case "right":
            return { w: length, h: height }
        default:
            return { w: width, h: length }
    }
}

export function hasSize(layout: Pick<DesignLayout, "kind" | "width" | "length" | "height"> | null | undefined): boolean {
    if (layout?.kind === "sheet") {
        return !!layout.width && !!layout.height
    }
    return !!layout && !!layout.width && !!layout.length && !!layout.height
}

/**
 * A label is one flat page; a box is laid out as its net.
 */
export function buildLayoutNet(layout: DesignLayout): BoxNet | null {
    if (!hasSize(layout)) {
        return null
    }
    if (layout.kind === "sheet") {
        return { width: layout.width!, height: layout.height!, panels: [{ key: "sheet", face: "sheet", x: 0, y: 0, w: layout.width!, h: layout.height! }] }
    }
    return buildBoxNet(layout.width!, layout.length!, layout.height!)
}

export function newElementId(): string {
    return Math.random().toString(36).slice(2, 10)
}


/** height ÷ width of each image, loaded once per URL */
export const imageRatios = reactive<Record<string, number>>({})

export function ensureImageRatio(url: string | null | undefined): void {
    if (!url || imageRatios[url] !== undefined) {
        return
    }
    imageRatios[url] = 0
    const image = new Image()
    image.onload = () => imageRatios[url] = image.naturalWidth ? image.naturalHeight / image.naturalWidth : 1
    image.onerror = () => imageRatios[url] = 1
    image.src = url
}

/**
 * Where a background image sits on a face, in millimetres: it always covers the face, `zoom` enlarges it
 * and `x` / `y` (0 to 1) choose which part shows, so it can never be dragged out of the face.
 */
export function coverBox(faceWidth: number, faceHeight: number, ratio: number, zoom = 1, x = 0.5, y = 0.5): { left: number, top: number, width: number, height: number } {
    const safeRatio = ratio > 0 ? ratio : faceHeight / Math.max(faceWidth, 1)
    let width = faceWidth
    let height = faceWidth * safeRatio
    if (height < faceHeight) {
        height = faceHeight
        width = faceHeight / safeRatio
    }
    width *= zoom
    height *= zoom

    return {
        width,
        height,
        left: -(width - faceWidth) * x,
        top: -(height - faceHeight) * y,
    }
}

/**
 * Each comment number gets its own colour: the hue turns by the golden angle per number, so neighbouring
 * numbers always look clearly different and the sequence never runs out.
 */
export function markupColour(number: number): string {
    return `hsl(${Math.round((number * 137.508) % 360)}, 70%, 42%)`
}


/** 1 px at 96 dpi in millimetres, for outline widths typed in px */
export const PX_TO_MM = 0.2646

export interface ParsedPaint {
    mode: "solid" | "linear" | "radial" | "none"
    from: string
    to: string
    angle: number
}

/**
 * A paint is a solid hex colour, "none", or a two-stop gradient written "linear:<angle>:#from:#to" or
 * "radial:#from:#to".
 */
export function parsePaint(value: string | null | undefined, fallback = "#111827"): ParsedPaint {
    if (!value) {
        return { mode: "solid", from: fallback, to: fallback, angle: 90 }
    }
    if (value === "none") {
        return { mode: "none", from: fallback, to: fallback, angle: 90 }
    }
    const linear = value.match(/^linear:(\d{1,3}):(#[0-9a-fA-F]{6}):(#[0-9a-fA-F]{6})$/)
    if (linear) {
        return { mode: "linear", angle: Number(linear[1]), from: linear[2], to: linear[3] }
    }
    const radial = value.match(/^radial:(#[0-9a-fA-F]{6}):(#[0-9a-fA-F]{6})$/)
    if (radial) {
        return { mode: "radial", angle: 90, from: radial[1], to: radial[2] }
    }
    return { mode: "solid", from: value, to: value, angle: 90 }
}

export function formatPaint(paint: ParsedPaint): string {
    switch (paint.mode) {
        case "none":
            return "none"
        case "linear":
            return `linear:${Math.round(((paint.angle % 360) + 360) % 360)}:${paint.from.toLowerCase()}:${paint.to.toLowerCase()}`
        case "radial":
            return `radial:${paint.from.toLowerCase()}:${paint.to.toLowerCase()}`
        default:
            return paint.from.toLowerCase()
    }
}

/** the CSS background for a paint */
export function paintCss(value: string | null | undefined, fallback = "#ffffff"): string {
    const paint = parsePaint(value, fallback)
    if (paint.mode === "none") {
        return "transparent"
    }
    if (paint.mode === "linear") {
        return `linear-gradient(${paint.angle}deg, ${paint.from}, ${paint.to})`
    }
    if (paint.mode === "radial") {
        return `radial-gradient(circle, ${paint.from}, ${paint.to})`
    }
    return paint.from
}

/** text coloured with a paint: a gradient is clipped to the letters */
export function textPaintStyle(value: string | null | undefined): Record<string, string> {
    const paint = parsePaint(value, "#111827")
    if (paint.mode === "solid" || paint.mode === "none") {
        return { color: paint.mode === "none" ? "transparent" : paint.from }
    }
    return {
        backgroundImage: paintCss(value),
        WebkitBackgroundClip: "text",
        backgroundClip: "text",
        color: "transparent",
    }
}

/*
 * Author Louis Perez
 * Created on 17-10-2026-13h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

import { reactive } from "vue"

export type FaceKey = "front" | "back" | "left" | "right" | "top" | "bottom"

export const FACE_KEYS: FaceKey[] = ["front", "back", "left", "right", "top", "bottom"]

export type ElementType = "text" | "image" | "zone"

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
}

export interface DesignFace {
    background: { color: string | null, image_file_id: number | null, x?: number, y?: number, zoom?: number }
    elements: DesignElement[]
}

export interface DesignLayout {
    units: "mm" | "cm"
    width: number | null
    length: number | null
    height: number | null
    faces: Record<FaceKey, DesignFace>
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

export function hasSize(layout: Pick<DesignLayout, "width" | "length" | "height"> | null | undefined): boolean {
    return !!layout && !!layout.width && !!layout.length && !!layout.height
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

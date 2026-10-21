/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

export const MODEL_COLORS: Record<string, string> = {
    linear: "#86857f",
    first_touch: "#2a78d6",
}

export const ORDER_TYPE_COLORS = {
    first: "#eb6834",
    repeat: "#6250d6",
}

export const BAR_TRACK_COLOR = "#f1f1ef"

export const CHANNEL_COLORS: Record<string, string> = {
    "google-ads": "#2a78d6",
    "newsletter": "#eb6834",
    "organic-google": "#1baf7a",
    "marketing-mailshot": "#eda100",
    "meta-ads": "#e87ba4",
    "organic-bing": "#008300",
    "bing-ads": "#6250d6",
    "referral": "#e34948",
}

export const OTHER_CHANNEL_COLOR = "#a8a7a0"

export const NO_FIRST_TOUCH_COLOR = "#d6d3d1"

export const channelColor = (type: string) =>
    type === "no_first_touch" ? NO_FIRST_TOUCH_COLOR : (CHANNEL_COLORS[type] ?? OTHER_CHANNEL_COLOR)

/*
 * Author Louis Perez
 * Created on 15-10-2026-23h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

export interface ColorClasses {
    icon: string
    active: string
    count: string
    soft: string
    link?: string
}

const palette: Record<string, ColorClasses> = {
    blue: { icon: "text-blue-500", active: "border-blue-400 bg-blue-50 text-blue-800", count: "bg-blue-100 text-blue-800", soft: "border-blue-200 bg-blue-50/60" },
    green: { icon: "text-green-600", active: "border-green-400 bg-green-50 text-green-800", count: "bg-green-100 text-green-800", soft: "border-green-200 bg-green-50/60" },
    amber: { icon: "text-amber-500", active: "border-amber-400 bg-amber-50 text-amber-800", count: "bg-amber-100 text-amber-800", soft: "border-amber-200 bg-amber-50/60" },
    red: { icon: "text-red-500", active: "border-red-400 bg-red-50 text-red-800", count: "bg-red-100 text-red-800", soft: "border-red-200 bg-red-50/60" },
    teal: { icon: "text-teal-600", active: "border-teal-400 bg-teal-50 text-teal-800", count: "bg-teal-100 text-teal-800", soft: "border-teal-200 bg-teal-50/60" },
    purple: { icon: "text-purple-500", active: "border-purple-400 bg-purple-50 text-purple-800", count: "bg-purple-100 text-purple-800", soft: "border-purple-200 bg-purple-50/60" },
    orange: { icon: "text-orange-500", active: "border-orange-300 bg-orange-50 text-orange-800", count: "bg-orange-100 text-orange-800", soft: "border-orange-200 bg-orange-50/60", link: "hover:border-orange-500 hover:bg-orange-100" },
    sky: { icon: "text-sky-500", active: "border-sky-300 bg-sky-50 text-sky-800", count: "bg-sky-100 text-sky-800", soft: "border-sky-200 bg-sky-50/60", link: "hover:border-sky-500 hover:bg-sky-100" },
    rose: { icon: "text-rose-500", active: "border-rose-300 bg-rose-50 text-rose-800", count: "bg-rose-100 text-rose-800", soft: "border-rose-200 bg-rose-50/60", link: "hover:border-rose-500 hover:bg-rose-100" },
    cyan: { icon: "text-cyan-600", active: "border-cyan-300 bg-cyan-50 text-cyan-800", count: "bg-cyan-100 text-cyan-800", soft: "border-cyan-200 bg-cyan-50/60", link: "hover:border-cyan-500 hover:bg-cyan-100" },
    gray: { icon: "text-gray-500", active: "border-gray-400 bg-gray-100 text-gray-800", count: "bg-gray-200 text-gray-800", soft: "border-gray-200 bg-gray-50/60" },
}

export const productDevelopmentColor = (color: string | undefined): ColorClasses => palette[color ?? "gray"] ?? palette.gray

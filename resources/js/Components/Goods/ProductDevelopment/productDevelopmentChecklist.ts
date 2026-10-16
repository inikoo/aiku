/*
 * Author Louis Perez
 * Created on 16-10-2026-23h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

import { routeType } from "@/types/route"

export type ChecklistInputType = "integer" | "decimal" | "dimensions" | "country" | "textarea" | "text"

export interface ChecklistDimensions {
    l?: number | null
    w?: number | null
    h?: number | null
    units?: string
    type?: string
}

export interface ChecklistRow {
    id: number
    label: string
    role: { value: string, label: string, icon: string, color: string }
    required: boolean
    state: "missing" | "complete" | "not_applicable"
    input_type: ChecklistInputType
    unit: string | null
    currency: string | null
    writes_to: string | null
    needs_supplier_product: boolean
    value: string | number | ChecklistDimensions | null
    na_reason: string | null
    saved_by: string | null
    saved_at: string | null
    can_edit: boolean
    can_reopen: boolean
}

export interface ChecklistSku {
    id: number
    code: string
    name: string
    supplier_product: { id: number, code: string } | null
    can_link_supplier_product: boolean
    rows: ChecklistRow[]
}

export interface ChecklistEditorData {
    family: ChecklistRow[]
    skus: ChecklistSku[]
    currency: string | null
    countries: { value: number, label: string }[]
    routes: {
        update: string
        supplier_product: string
        supplier_products_search: routeType | null
    }
}

<!--
  - Author Louis Perez
  - Created on 20-10-2026-10h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faPlus, faChevronRight, faPlayCircle, faPauseCircle, faCheckCircle, faBan, faSparkles, faPaintBrush, faTag } from "@fal"
library.add(faLightbulbOn, faPlus, faChevronRight, faPlayCircle, faPauseCircle, faCheckCircle, faBan, faSparkles, faPaintBrush, faTag)

export interface RelatedProductDevelopment {
    id: number
    code: string
    name: string
    type: { label: string, icon: string, color: string }
    status: { label: string, icon: string, color: string }
    stage: string | null
    missing: number
    owner: string | null
    target_online_date: string | null
    route: routeType
    linked?: "auto" | "manual" | null
}

defineProps<{
    data: { rows: RelatedProductDevelopment[], create_route?: routeType | null } | null
    compact?: boolean
    emptyText?: string
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : null
</script>

<template>
    <div v-if="data" :class="compact ? '' : 'p-4'">
        <ul class="divide-y divide-gray-100" :class="compact ? '' : 'overflow-hidden rounded-lg border border-gray-200 bg-white'">
            <li v-for="development in data.rows" :key="development.id">
                <Link :href="route(development.route.name, development.route.parameters)" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-gray-50">
                    <FontAwesomeIcon :icon="development.type.icon" :class="productDevelopmentColor(development.type.color).icon" class="shrink-0" fixed-width :aria-label="development.type.label" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm">
                            <span class="font-medium text-gray-900">{{ development.code }}</span>
                            <span class="ml-1.5 text-gray-600">{{ development.name }}</span>
                        </span>
                        <span class="block truncate text-xs text-gray-500">
                            {{ [development.stage, development.owner, development.target_online_date ? ctrans("online :date", { date: formatDate(development.target_online_date) ?? "" }) : null].filter(Boolean).join(" · ") }}
                        </span>
                    </span>
                    <span v-if="development.linked === 'manual'" class="hidden shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 sm:inline">{{ ctrans("Linked by hand") }}</span>
                    <span v-if="development.missing" class="hidden shrink-0 rounded bg-amber-50 px-1.5 py-0.5 text-xs text-amber-800 sm:inline">{{ ctrans(":count missing", { count: String(development.missing) }) }}</span>
                    <span class="flex shrink-0 items-center gap-1 rounded border px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(development.status.color).active">
                        <FontAwesomeIcon :icon="development.status.icon" fixed-width aria-hidden="true" />{{ development.status.label }}
                    </span>
                    <FontAwesomeIcon icon="fal fa-chevron-right" class="shrink-0 text-xs text-gray-300" fixed-width aria-hidden="true" />
                </Link>
            </li>
            <li v-if="!data.rows.length" class="px-4 py-6 text-center text-sm text-gray-400">{{ emptyText ?? ctrans("No product development yet") }}</li>
        </ul>
        <div v-if="data.create_route && !compact" class="mt-3 flex justify-end">
            <Link :href="route(data.create_route.name, data.create_route.parameters)" class="inline-flex items-center gap-1.5 text-sm text-[--app-accent] hover:text-[--app-accent-strong]">
                <FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" />{{ ctrans("New development") }}
            </Link>
        </div>
    </div>
</template>

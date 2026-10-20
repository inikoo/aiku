<!--
  - Author Louis Perez
  - Created on 18-10-2026-00h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import ProductDevelopmentWaitingList, { type WaitingRow } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentWaitingList.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn } from "@fal"
library.add(faLightbulbOn)

export interface ProductDevelopmentWaitingWidgetData {
    rows: WaitingRow[]
    total: number
    route: routeType
}

defineProps<{
    data: ProductDevelopmentWaitingWidgetData
}>()
</script>

<template>
    <section class="rounded-lg bg-white shadow ring-1 ring-gray-200">
        <header class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
            <FontAwesomeIcon icon="fal fa-lightbulb-on" class="text-[--app-accent]" fixed-width aria-hidden="true" />
            <h2 class="flex-1 text-sm font-semibold text-gray-900">{{ ctrans("Product development · waiting on me") }}</h2>
            <span v-if="data.total" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">{{ data.total }}</span>
        </header>
        <div class="p-3">
            <ProductDevelopmentWaitingList :rows="data.rows" class="[&_section]:rounded-md [&_section]:border-0" />
        </div>
        <footer v-if="data.total > data.rows.length" class="border-t border-gray-100 px-4 py-2 text-right">
            <Link :href="route(data.route.name, data.route.parameters)" class="primaryLink text-sm">{{ ctrans("See all :count", { count: String(data.total) }) }}</Link>
        </footer>
    </section>
</template>

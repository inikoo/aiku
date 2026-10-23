<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import type { JourneySource, OrderJourney } from "@/types/Components/SeoJourney"

defineProps<{
    journey: OrderJourney
}>()

const locale = useLocaleStore()

const sourceText = (source: JourneySource) => source.reference ? `${source.label}: ${source.reference}` : source.label
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
            <Link :href="route(journey.customer.route.name, journey.customer.route.parameters)" class="primaryLink font-medium">
                {{ journey.customer.name }}
            </Link>
            <Link :href="route(journey.order_route.name, journey.order_route.parameters)" class="primaryLink">
                {{ journey.reference }}
            </Link>
            <span class="tabular-nums text-gray-900">
                {{ journey.currency_code ? locale.currencyFormat(journey.currency_code, journey.net_amount) : locale.number(journey.net_amount) }}
            </span>
            <span class="tabular-nums text-gray-500">{{ useFormatTime(journey.submitted_at, { formatTime: "hm" }) }}</span>
        </div>

        <section>
            <h3 class="mb-2 text-sm font-semibold text-gray-900">{{ ctrans("Visits before the order") }}</h3>
            <p v-if="!journey.visits.length" class="text-sm text-gray-500">{{ ctrans("No visits were tracked for this order.") }}</p>
            <ol v-else class="space-y-3">
                <li v-for="(visit, index) in journey.visits" :key="visit.id" class="rounded-lg border border-gray-200 bg-white">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                        <span class="font-medium text-gray-900">{{ ctrans("Visit :number", { number: index + 1 }) }}</span>
                        <span class="rounded border border-[--app-accent-muted] bg-[--app-accent-soft] px-1.5 text-xs text-gray-700">{{ sourceText(visit.source) }}</span>
                        <span class="tabular-nums text-gray-500">{{ useFormatTime(visit.started_at, { formatTime: "hm" }) }}</span>
                        <span v-if="visit.landing_path" class="break-all text-gray-500">{{ ctrans("Landed on :path", { path: visit.landing_path }) }}</span>
                    </div>
                    <ul v-if="visit.pages.length" class="px-3 py-2">
                        <li v-for="(page, pageIndex) in visit.pages" :key="`${visit.id}-${pageIndex}`" class="flex flex-wrap items-baseline gap-x-3 py-0.5 text-sm">
                            <span class="w-40 shrink-0 tabular-nums text-gray-500">{{ useFormatTime(page.viewed_at, { formatTime: "hms" }) }}</span>
                            <span class="break-all text-gray-900">{{ page.path }}</span>
                            <span v-if="page.type" class="rounded border border-gray-200 bg-gray-50 px-1.5 text-xs text-gray-600">{{ page.type }}</span>
                        </li>
                    </ul>
                    <p v-else class="px-3 py-2 text-sm text-gray-500">{{ ctrans("No pages were recorded before the order in this visit.") }}</p>
                </li>
            </ol>
        </section>
    </div>
</template>

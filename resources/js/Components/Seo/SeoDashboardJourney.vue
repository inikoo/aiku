<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Link, router, usePage } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import Dialog from "primevue/dialog"
import SeoDashboardJourneyDetail from "@/Components/Seo/SeoDashboardJourneyDetail.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import type { JourneyOrder, JourneySource, OrderJourney } from "@/types/Components/SeoJourney"

defineOptions({ inheritAttrs: false })

const props = defineProps<{
    data?: { days: number, orders: JourneyOrder[] } | null
    tab: string
}>()

const locale = useLocaleStore()
const page = usePage()

const query = new URLSearchParams(window.location.search)
const selectedDays = ref(query.get("journey_days") ?? "30")
const isLoading = ref(false)
const selectedPurchaseId = ref<number | null>(null)
const isDetailOpen = ref(false)

const dayOptions = [
    { label: ctrans(":days days", { days: 30 }), value: "30" },
    { label: ctrans(":days days", { days: 90 }), value: "90" },
    { label: ctrans(":days days", { days: 180 }), value: "180" },
]

const journeyUrl = (extra = "") => `${window.location.pathname}?tab=${props.tab}&journey_days=${selectedDays.value}${extra}`

watch(selectedDays, () => {
    router.get(journeyUrl(), {}, {
        only: [props.tab],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => isLoading.value = true,
        onFinish: () => isLoading.value = false,
    })
})

const detail = computed(() => {
    const journey = page.props.journey_order as OrderJourney | null | undefined

    return journey && journey.purchase_id === selectedPurchaseId.value ? journey : null
})

const openJourney = (purchaseId: number) => {
    selectedPurchaseId.value = purchaseId
    isDetailOpen.value = true

    router.get(journeyUrl(`&journey_order=${purchaseId}`), {}, {
        only: ["journey_order"],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const sourceText = (source: JourneySource) => source.reference ? `${source.label}: ${source.reference}` : source.label

const collapsedSources = (sources: JourneySource[]) => sources.reduce<{ text: string, count: number }[]>((groups, source) => {
    const text = sourceText(source)
    const last = groups[groups.length - 1]

    if (last && last.text === text) {
        last.count++
    } else {
        groups.push({ text, count: 1 })
    }

    return groups
}, [])

const money = (order: JourneyOrder) => order.currency_code
    ? String(locale.currencyFormat(order.currency_code, order.net_amount))
    : locale.number(order.net_amount)
</script>

<template>
    <div class="px-4 pb-8 pt-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-3xl text-sm text-gray-600">
                {{ ctrans("The latest orders placed on the website, with the traffic source of every visit that led to them. Open an order to see each visit and the pages the customer went through before buying.") }}
            </p>
            <SegmentedToggle v-model="selectedDays" :options="dayOptions" :ariaLabel="ctrans('Period')" />
        </div>

        <div v-if="!data" class="space-y-3" aria-busy="true">
            <div class="h-4 w-64 animate-pulse rounded bg-gray-200" />
            <div class="h-96 w-full animate-pulse rounded bg-gray-100" />
        </div>

        <template v-else>
            <p v-if="!data.orders.length" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
                {{ ctrans("No tracked purchases in this period.") }}
            </p>

            <div v-else class="overflow-x-auto rounded-lg border border-gray-200 bg-white transition-opacity" :class="{ 'opacity-50': isLoading }">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-2">{{ ctrans("Order") }}</th>
                            <th class="px-4 py-2">{{ ctrans("Customer") }}</th>
                            <th class="px-4 py-2 text-right">{{ ctrans("Net") }}</th>
                            <th class="px-4 py-2">{{ ctrans("Placed") }}</th>
                            <th class="px-4 py-2 text-right">{{ ctrans("Visits") }}</th>
                            <th class="px-4 py-2">{{ ctrans("Sources") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="order in data.orders"
                            :key="order.purchase_id"
                            class="cursor-pointer hover:bg-[--app-accent-soft] focus-visible:bg-[--app-accent-soft]"
                            tabindex="0"
                            @click="openJourney(order.purchase_id)"
                            @keydown.enter="openJourney(order.purchase_id)">
                            <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ order.reference }}</td>
                            <td class="px-4 py-2">
                                <Link :href="route(order.customer.route.name, order.customer.route.parameters)" class="primaryLink" @click.stop>
                                    {{ order.customer.name }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right tabular-nums">{{ money(order) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 tabular-nums text-gray-500">{{ useFormatTime(order.submitted_at, { formatTime: "hm" }) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ order.visits }}</td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap items-center gap-1">
                                    <span
                                        v-for="(group, index) in collapsedSources(order.sources)"
                                        :key="`${order.purchase_id}-${index}`"
                                        class="rounded border border-[--app-accent-muted] bg-[--app-accent-soft] px-1.5 text-xs text-gray-700">
                                        {{ group.text }}<template v-if="group.count > 1"> ×{{ group.count }}</template>
                                    </span>
                                    <span v-if="!order.sources.length" class="text-gray-400">-</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <Dialog v-model:visible="isDetailOpen" modal dismissableMask :header="ctrans('Order journey')" class="w-[min(1180px,96vw)]">
            <SeoDashboardJourneyDetail v-if="detail" :journey="detail" />
            <div v-else class="space-y-3" aria-busy="true">
                <div class="h-4 w-64 animate-pulse rounded bg-gray-200" />
                <div class="h-72 w-full animate-pulse rounded bg-gray-100" />
            </div>
        </Dialog>
    </div>
</template>

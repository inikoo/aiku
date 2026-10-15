<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, inject } from "vue"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"

type FillRate = { key: string; label: string; filled: number; total: number; scope: "products" | "suppliers" }

const props = defineProps<{
    data: {
        kpis: {
            total: number
            ready_to_cost: number
            missing_cbm: number
            missing_moq_or_weights: number
            not_available: number
            not_available_without_reason: number
            under_generic: number
            without_family: number
            suppliers: number
            generic_suppliers: number
            lead_time: { products: number; average_estimated: number | null; average_measured: number | null; arrived_late: number }
        }
        fill_rates: FillRate[]
    }
}>()

const locale = inject("locale", aikuLocaleStructure)
const number = (value: number) => locale.number(value)
const percentage = (part: number, total: number) => (total ? Math.round((part / total) * 100) : 0)

const organisation = route().params["organisation"]
const productsRoute = (focus?: string) =>
    route("grp.org.agent.org_supplier_products.index", { organisation, tab: "index", ...(focus ? { "index_filter[focus]": focus } : {}) })
const suppliersRoute = (focus?: string) =>
    route("grp.org.agent.org_supplier_products.index", { organisation, tab: "suppliers", ...(focus ? { "suppliers_filter[focus]": focus } : {}) })

const kpis = computed(() => props.data.kpis)

const tiles = computed(() => [
    {
        key: "ready",
        label: ctrans("Ready to cost freight"),
        value: kpis.value.ready_to_cost,
        note: ctrans(":percentage% have CBM and lead time", { percentage: percentage(kpis.value.ready_to_cost, kpis.value.total) }),
        href: productsRoute(),
    },
    {
        key: "missing_cbm",
        label: ctrans("Missing CBM"),
        value: kpis.value.missing_cbm,
        note: ctrans(":percentage% of products", { percentage: percentage(kpis.value.missing_cbm, kpis.value.total) }),
        href: productsRoute("missing_cbm"),
    },
    {
        key: "missing_moq_or_weights",
        label: ctrans("Missing MOQ or carton weights"),
        value: kpis.value.missing_moq_or_weights,
        note: ctrans(":percentage% of products", { percentage: percentage(kpis.value.missing_moq_or_weights, kpis.value.total) }),
        href: null,
    },
    {
        key: "not_available",
        label: ctrans("Not available"),
        value: kpis.value.not_available,
        note: kpis.value.not_available_without_reason
            ? ctrans(":count with no reason recorded", { count: number(kpis.value.not_available_without_reason) })
            : ctrans("All with a reason"),
        href: productsRoute("not_available"),
    },
    {
        key: "under_generic",
        label: ctrans("Under generic records"),
        value: kpis.value.under_generic,
        note: ctrans(":count generic suppliers", { count: kpis.value.generic_suppliers }),
        href: productsRoute("generic"),
    },
])

const productRates = computed(() => props.data.fill_rates.filter((rate) => rate.scope === "products"))
const supplierRates = computed(() => props.data.fill_rates.filter((rate) => rate.scope === "suppliers"))
const leadTime = computed(() => kpis.value.lead_time)
const leadTimeGap = computed(() =>
    leadTime.value.average_estimated && leadTime.value.average_measured ? leadTime.value.average_measured - leadTime.value.average_estimated : null
)
</script>

<template>
    <div class="space-y-6 p-6">
        <div class="text-sm text-gray-500">
            {{ ctrans(":products active products from :suppliers suppliers", { products: number(kpis.total), suppliers: number(kpis.suppliers) }) }}
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <component :is="tile.href ? Link : 'div'" v-for="tile in tiles" :key="tile.key" :href="tile.href ?? undefined"
                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" :class="tile.href ? 'hover:border-gray-400' : ''">
                <div class="text-xs text-gray-500">{{ tile.label }}</div>
                <div class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">{{ number(tile.value) }}</div>
                <div class="mt-0.5 text-xs text-gray-500">{{ tile.note }}</div>
            </component>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Data filled in") }}</h3>
                <div class="space-y-2">
                    <div v-for="rate in productRates" :key="rate.key" class="grid grid-cols-[11rem_minmax(0,1fr)_7rem] items-center gap-3 text-sm"
                        v-tooltip="ctrans(':filled of :total products', { filled: number(rate.filled), total: number(rate.total) })">
                        <span class="text-gray-600">{{ rate.label }}</span>
                        <span class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <span class="block h-full rounded-full bg-gray-700" :style="{ width: `${percentage(rate.filled, rate.total)}%` }" />
                        </span>
                        <span class="text-right tabular-nums text-gray-700">{{ percentage(rate.filled, rate.total) }}%
                            <span class="text-xs text-gray-400">{{ number(rate.filled) }}</span>
                        </span>
                    </div>
                </div>
                <h3 class="mb-3 mt-5 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Supplier contacts") }}</h3>
                <div class="space-y-2">
                    <Link v-for="rate in supplierRates" :key="rate.key" :href="rate.key === 'email' ? suppliersRoute('no_email') : suppliersRoute()"
                        class="grid grid-cols-[11rem_minmax(0,1fr)_7rem] items-center gap-3 text-sm hover:bg-gray-50"
                        v-tooltip="ctrans(':filled of :total suppliers', { filled: number(rate.filled), total: number(rate.total) })">
                        <span class="text-gray-600">{{ rate.label }}</span>
                        <span class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <span class="block h-full rounded-full bg-gray-700" :style="{ width: `${percentage(rate.filled, rate.total)}%` }" />
                        </span>
                        <span class="text-right tabular-nums text-gray-700">{{ percentage(rate.filled, rate.total) }}%
                            <span class="text-xs text-gray-400">{{ number(rate.filled) }}</span>
                        </span>
                    </Link>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Lead time, estimated vs measured") }}</h3>
                <template v-if="leadTime.products">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">{{ ctrans("Estimated") }}</div>
                            <div class="text-2xl font-semibold tabular-nums text-gray-900">{{ ctrans(":days days", { days: leadTime.average_estimated }) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">{{ ctrans("Measured") }}</div>
                            <div class="text-2xl font-semibold tabular-nums text-gray-900">{{ ctrans(":days days", { days: leadTime.average_measured }) }}</div>
                        </div>
                    </div>
                    <p v-if="leadTimeGap !== null && leadTimeGap > 0" class="mt-3 text-sm text-gray-700">
                        {{ ctrans("Goods arrive :days days later than estimated on average.", { days: leadTimeGap }) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-700">
                        {{ ctrans(":late of :products products arrived more than 25% late.", { late: number(leadTime.arrived_late), products: number(leadTime.products) }) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">{{ ctrans("Products with both an estimate and at least one measured delivery.") }}</p>
                </template>
                <p v-else class="text-sm italic text-gray-400">{{ ctrans("No product has both an estimate and a measured delivery yet.") }}</p>
            </section>
        </div>
    </div>
</template>

<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { ref } from "vue"
import { Link, router } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import InputText from "primevue/inputtext"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSearch, faUser } from "@fal"
import { MODEL_COLORS, channelColor } from "@/Components/Marketing/V2/palette"

library.add(faSearch, faUser)

type ModelKey = "linear" | "first_touch"

type Split = { type: string, name: string, share: number }

const props = defineProps<{
    data: {
        query: string
        found: boolean
        currency_code: string
        rules: {
            models: { key: ModelKey, label: string, description: string }[]
            window_days: number
            acquisition_range_days: number
            new_customer_window: number
            existing_customer_window: number
        }
        customer?: {
            name: string
            reference: string
            registered_at: string
            acquisition: { type: string, name: string, date: string } | null
            orders: number
            orders_value: number
            route: routeType
        }
        orders?: {
            reference: string
            placed_at: string
            net_amount: number
            is_selected: boolean
            is_first: boolean
            route: routeType
            omitted_touches: number
            touches: {
                date: string
                type: string
                name: string
                campaign: string | null
                is_acquisition: boolean
                shares: Record<ModelKey, number | null>
            }[]
            splits: Record<ModelKey, Split[]>
        }[]
    }
}>()

const locale = useLocaleStore()
const money = (value: number) => locale.currencyFormat(props.data.currency_code, value)
const share = (value: number | null) => value === null ? "—" : Math.round(value * 100) + "%"
const date = (value: string) => useFormatTime(value)

const search = ref(props.data.query ?? "")
const isSearching = ref(false)

const submit = () => {
    router.get(
        window.location.pathname,
        { tab: "journey", order: search.value.trim() },
        {
            only: ["journey"],
            preserveState: true,
            preserveScroll: true,
            onStart: () => { isSearching.value = true },
            onFinish: () => { isSearching.value = false },
        }
    )
}

const modelLabel = (key: ModelKey) => props.data.rules.models.find(model => model.key === key)?.label ?? key
</script>

<template>
    <div class="px-4 py-5 md:px-6 space-y-6">
        <form class="flex flex-wrap items-end gap-3" @submit.prevent="submit">
            <label class="flex flex-col gap-1 text-xs text-gray-500">
                {{ ctrans("Find order") }}
                <InputText v-model="search" :placeholder="ctrans('Order number')" class="w-64" />
            </label>
            <Button type="tertiary" nativeType="submit" icon="fal fa-search" :label="ctrans('Show journey')" :loading="isSearching" />
        </form>

        <div v-if="!data.query" class="rounded-xl ring-1 ring-gray-200 bg-white py-10 text-center text-sm text-gray-500">
            {{ ctrans("Enter an order number to see how its credit is split") }}
        </div>
        <div v-else-if="!data.found" class="rounded-xl ring-1 ring-amber-200 bg-amber-50 py-6 text-center text-sm text-amber-800">
            {{ ctrans("No order :reference in this shop", { reference: data.query }) }}
        </div>

        <template v-else-if="data.customer && data.orders">
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-[--app-accent] p-5 text-[--app-accent-text]" aria-label="Customer">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-white/20" aria-hidden="true">
                        <FontAwesomeIcon icon="fal fa-user" class="text-xl" />
                    </span>
                    <div>
                        <Link :href="route(data.customer.route.name, data.customer.route.parameters)" class="text-sm opacity-90 hover:underline">
                            {{ data.customer.name }} · {{ data.customer.reference }}
                        </Link>
                        <div class="text-xs opacity-80">{{ ctrans("Acquired by") }}</div>
                        <div class="text-xl font-semibold">{{ data.customer.acquisition?.name ?? ctrans("No first touch") }}</div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span v-if="data.customer.acquisition" class="rounded-full bg-white/20 px-3 py-1.5">{{ ctrans("First touch") }} {{ date(data.customer.acquisition.date) }}</span>
                    <span class="rounded-full bg-white/20 px-3 py-1.5">{{ ctrans("Registered") }} {{ date(data.customer.registered_at) }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5 font-medium text-[--app-accent-deep]">
                        {{ locale.number(data.customer.orders) }} {{ ctrans("orders") }} · {{ money(data.customer.orders_value) }}
                    </span>
                </div>
            </section>

            <section v-for="order in data.orders" :key="order.reference"
                     class="rounded-xl bg-white p-5"
                     :class="order.is_selected ? 'ring-2 ring-[--app-accent]' : 'ring-1 ring-gray-200'">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <Link :href="route(order.route.name, order.route.parameters)" class="whitespace-nowrap text-sm font-semibold text-gray-900 hover:underline">{{ order.reference }}</Link>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                              :class="order.is_first ? 'bg-orange-50 text-orange-800' : 'bg-violet-50 text-violet-800'">
                            {{ order.is_first ? ctrans("First order") : ctrans("Repeat order") }}
                        </span>
                        <span class="text-xs text-gray-500">{{ date(order.placed_at) }}</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 tabular-nums">{{ money(order.net_amount) }}</span>
                </div>

                <div v-if="order.touches.length" class="mt-5 overflow-x-auto">
                    <div class="relative flex min-w-max gap-2 pb-1">
                        <div class="absolute left-12 right-12 top-8 h-0.5 bg-gray-200" aria-hidden="true" />
                        <div v-for="(touch, index) in order.touches" :key="index"
                             v-tooltip="touch.campaign ? `${touch.name} · ${touch.campaign}` : touch.name"
                             class="relative flex w-28 flex-col items-center gap-1 text-center"
                             :class="touch.shares.first_touch === null && touch.shares.linear === null ? 'opacity-50' : ''">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full text-sm font-semibold text-white ring-4 ring-white tabular-nums"
                                 :style="{ background: channelColor(touch.type) }">
                                {{ share(touch.shares.first_touch) }}
                            </div>
                            <span class="text-xs text-gray-500 tabular-nums">{{ modelLabel("linear") }} {{ share(touch.shares.linear) }}</span>
                            <span class="text-sm font-medium text-gray-800">{{ touch.name }}</span>
                            <span class="text-xs text-gray-500">{{ date(touch.date) }}</span>
                            <span v-if="touch.is_acquisition" class="rounded-full bg-[--app-accent-soft] px-2 py-0.5 text-xs font-medium text-[--app-accent-deep]">{{ ctrans("Acquisition") }}</span>
                        </div>
                    </div>
                </div>
                <p v-if="order.omitted_touches > 0" class="mt-2 text-xs text-gray-400">
                    {{ ctrans(":count earlier touches without credit are not shown", { count: order.omitted_touches }) }}
                </p>

                <div class="mt-5 space-y-2">
                    <div v-for="model in data.rules.models" :key="model.key" class="grid grid-cols-[6.5rem_minmax(0,1fr)] items-center gap-3">
                        <span v-tooltip="model.description" class="cursor-help text-xs font-medium" :style="{ color: MODEL_COLORS[model.key] }">{{ model.label }}</span>
                        <div v-if="order.splits[model.key].length" class="flex h-7 gap-[2px] overflow-hidden rounded-md">
                            <div v-for="split in order.splits[model.key]" :key="split.type"
                                 v-tooltip="`${split.name} ${share(split.share)}`"
                                 class="flex items-center justify-center overflow-hidden whitespace-nowrap px-1 text-xs font-medium text-white"
                                 :style="{ width: `${split.share * 100}%`, background: channelColor(split.type) }">
                                {{ split.share >= 0.2 ? `${split.name} ${share(split.share)}` : "" }}
                            </div>
                        </div>
                        <div v-else class="flex h-7 items-center rounded-md bg-amber-50 px-3 text-xs text-amber-800">{{ ctrans("No touch") }}</div>
                    </div>
                </div>
            </section>
        </template>

        <section class="space-y-3" aria-labelledby="v2-rules">
            <h2 id="v2-rules" class="text-sm font-medium text-gray-800">{{ ctrans("Rules") }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                <div class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">{{ modelLabel("linear") }}</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ ctrans("Even split") }}</div>
                    <div class="text-xs text-gray-500">{{ ctrans(":days days after each touch", { days: data.rules.window_days }) }}</div>
                </div>
                <div class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">{{ ctrans("Acquisition touch") }}</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ ctrans(":days days", { days: data.rules.acquisition_range_days }) }}</div>
                    <div class="text-xs text-gray-500">{{ ctrans("earliest touch before registering") }}</div>
                </div>
                <div class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">{{ modelLabel("first_touch") }} · {{ ctrans("new customer") }}</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ ctrans(":days days", { days: data.rules.new_customer_window }) }}</div>
                    <div class="text-xs text-gray-500">{{ ctrans("after the acquisition touch") }}</div>
                </div>
                <div class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                    <div class="text-xs text-gray-500">{{ modelLabel("first_touch") }} · {{ ctrans("existing customer") }}</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900">{{ ctrans(":days days", { days: data.rules.existing_customer_window }) }}</div>
                    <div class="text-xs text-gray-500">{{ ctrans("after the first recorded touch") }}</div>
                </div>
                <div class="rounded-xl ring-1 ring-green-200 bg-green-50 p-4">
                    <div class="text-xs text-green-800">{{ ctrans("Per order") }}</div>
                    <div class="mt-1 text-lg font-semibold text-green-900">Σ 1.00</div>
                    <div class="text-xs text-green-800">{{ ctrans("direct visits are not touches") }}</div>
                </div>
            </div>
        </section>
    </div>
</template>

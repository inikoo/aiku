<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 10 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { computed, ref } from "vue"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import AgentDraftReadyCell from "@/Components/Procurement/AgentDraftReadyCell.vue"
import AgentDraftSendActions from "@/Components/Procurement/AgentDraftSendActions.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Image from "@common/Components/Image.vue"
import type { Image as ImageTS } from "@/types/Image"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import type { Navigation } from "@/types/Tabs"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPaperPlane, faExclamationTriangle, faCheck, faHourglassHalf, faChevronRight, faChevronDown, faExternalLink } from "@fal"

library.add(faPaperPlane, faExclamationTriangle, faCheck, faHourglassHalf, faChevronRight, faChevronDown, faExternalLink)

type Part = {
    purchase_order_id: number
    reference: string
    organisation_code: string
    organisation_name: string
    is_ours: boolean
    lines: number
    amount: number
    cbm: number | null
    lines_without_cbm: number
    ready: boolean
    ready_by: string | null
    ready_at: string | null
    url: string | null
}

type View = "cards" | "orders"

type Line = {
    id: number
    image: ImageTS | null
    code: string
    name: string
    units: number
    cartons: number | null
    skos: number | null
    unit_cost: number | null
    amount: number
    cbm: number | null
}

type Card = {
    supplier_id: number
    supplier_code: string
    supplier_name: string
    currency: string | null
    org_exchange: number | null
    lines: number
    amount: number
    cbm: number
    lines_without_cbm: number
    ready_count: number
    all_ready: boolean
    has_ours: boolean
    others_asked: { by: string | null, at: string, told: number } | null
    parts: Part[]
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    agentName: string
    orgCurrency: string
    container: { label: string, short: string, cbm: number }
    canSend: boolean
    sendRoute: routeType
    tellRoute: routeType
    tabs: { current: View, navigation: Navigation }
    lines?: Record<number, Line[]>
    cards: Card[]
}>()

const scope = ref<string | null>("ours")
const oursCount = computed(() => props.cards.filter((card) => card.has_ours).length)
const shownCards = computed(() => (scope.value === "ours" ? props.cards.filter((card) => card.has_ours) : props.cards))

const currentView = computed(() => props.tabs.current)

const changeView = (view: string) => {
    if (view === currentView.value) {
        return
    }
    router.get(window.location.pathname, { view }, { only: ["tabs", "lines"], preserveState: true, preserveScroll: true, replace: true })
}

const MINIMUM_REASON_LENGTH = 10

const locale = useLocaleStore()
const fieldFocusClass = "[&.p-focus]:!border-gray-500 [&_textarea:focus]:!border-gray-500"

const money = (card: Card, amount: number) => (card.currency ? locale.currencyFormat(card.currency, amount) : locale.number(amount))
const orgMoney = (card: Card, amount: number) => (card.org_exchange ? locale.currencyFormat(props.orgCurrency, amount * card.org_exchange) : null)
const volume = (cbm: number | null) => (cbm === null ? "—" : `${locale.number(cbm)} m³`)

const waitingParts = (card: Card) => card.parts.filter((part) => !part.ready && !part.is_ours)
const isAgreed = (card: Card) => card.has_ours && waitingParts(card).length === 0
const agreedCards = computed(() => props.cards.filter(isAgreed))

const containerShare = (cbm: number) => `${locale.number(Math.round((cbm / props.container.cbm) * 1000) / 10)}%`
const openOrders = ref<Set<number>>(new Set())
const ownOrder = (card: { parts: { url: string | null; reference: string }[] }) => card.parts.find((part) => part.url)

const shownOrderIds = computed(() => shownCards.value.flatMap((card) => card.parts.map((part) => part.purchase_order_id)))
const isEveryOrderOpen = computed(() => shownOrderIds.value.length > 0 && shownOrderIds.value.every((id) => openOrders.value.has(id)))
const toggleOrder = (id: number) => {
    const next = new Set(openOrders.value)
    next.has(id) ? next.delete(id) : next.add(id)
    openOrders.value = next
}
const toggleEveryOrder = () => {
    openOrders.value = isEveryOrderOpen.value ? new Set() : new Set(shownOrderIds.value)
}

const ourPart = (card: Card) => card.parts.find((part) => part.is_ours)

const isSending = ref(false)
const reload = {
    preserveScroll: true,
    get only() {
        return currentView.value === "orders" || previewed.value ? ["cards", "lines", "pageHead"] : ["cards", "pageHead"]
    },
}

const onError = (errors: Record<string, string>) =>
    notify({
        title: ctrans("Not submitted"),
        text: errors.override_reason ?? errors.drafts ?? errors.ready ?? ctrans("Something went wrong, please try again"),
        type: "error",
    })

const setReady = (part: Part, ready: boolean) => {
    router.patch(route("grp.models.purchase-order.ready_to_send", { purchaseOrder: part.purchase_order_id }), { ready }, { ...reload, onError })
}

const send = (supplierIds: number[], overrideReason: string | null = null) => {
    router.post(
        route(props.sendRoute.name, props.sendRoute.parameters),
        { supplier_ids: supplierIds, ...(overrideReason ? { override_reason: overrideReason } : {}) },
        {
            ...reload,
            onStart: () => (isSending.value = true),
            onFinish: () => (isSending.value = false),
            onSuccess: () => (overrideCard.value = null),
            onError,
        }
    )
}

const isOnlyOurs = (card: Card) => card.has_ours && card.parts.length === 1

const tellOthers = (card: Card) => {
    router.post(route(props.tellRoute.name, props.tellRoute.parameters), { supplier_id: card.supplier_id }, { ...reload, onError })
}

const overrideCard = ref<Card | null>(null)
const overrideReason = ref("")
const isOverrideOpen = computed({
    get: () => overrideCard.value !== null,
    set: (isOpen: boolean) => {
        if (!isOpen) {
            overrideCard.value = null
        }
    },
})

const openOverride = (card: Card) => {
    overrideReason.value = ""
    overrideCard.value = card
}

type PreviewLine = Line & { key: string, organisations: string[] }

const addKnown = (total: number | null, value: number | null) => (total === null ? value : value === null ? total : total + value)
const previewFailed = ref(false)

const previewed = ref<{ card: Card, part: Part | null } | null>(null)
const isLoadingPreview = ref(false)
const isPreviewOpen = computed({
    get: () => previewed.value !== null,
    set: (isOpen: boolean) => {
        if (!isOpen) {
            previewed.value = null
        }
    },
})
const previewLines = computed<PreviewLine[]>(() => {
    if (!previewed.value) {
        return []
    }
    const { card, part } = previewed.value
    if (part) {
        return (props.lines?.[part.purchase_order_id] ?? []).map((line) => ({ ...line, key: String(line.id), organisations: [] }))
    }
    const combined = new Map<string, PreviewLine>()
    for (const cardPart of card.parts) {
        for (const line of props.lines?.[cardPart.purchase_order_id] ?? []) {
            const row = combined.get(line.code)
            if (!row) {
                combined.set(line.code, { ...line, key: line.code, organisations: [cardPart.organisation_code] })
                continue
            }
            row.cartons = addKnown(row.cartons, line.cartons)
            row.skos = addKnown(row.skos, line.skos)
            row.amount += line.amount
            row.cbm = addKnown(row.cbm, line.cbm)
            row.organisations.push(cardPart.organisation_code)
        }
    }
    return [...combined.values()].sort((a, b) => a.code.localeCompare(b.code))
})
const previewTotals = computed(() => {
    const { card, part } = previewed.value!
    return { lines: previewLines.value.length, amount: part ? part.amount : card.amount, cbm: part ? part.cbm : card.cbm }
})

const openPreview = (card: Card, part: Part | null = null) => {
    previewed.value = { card, part }
    previewFailed.value = false
    if (currentView.value === "orders") {
        return
    }
    isLoadingPreview.value = true
    router.reload({
        only: ["lines"],
        onError: () => (previewFailed.value = true),
        onFinish: (visit) => {
            isLoadingPreview.value = false
            previewFailed.value ||= visit.cancelled || visit.interrupted
        },
    })
}

const isReasonGiven = computed(() => overrideReason.value.trim().length >= MINIMUM_REASON_LENGTH)
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentView" :navigation="tabs.navigation" @update:tab="changeView" />

    <div class="mx-4 mt-4 flex flex-col gap-4 pb-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <PillFilterBar
                :label="ctrans('Suppliers')"
                :options="[{ value: 'ours', label: ctrans('With products of my organisation'), count: oursCount }]"
                :selected="scope"
                @select="(value: string | null) => (scope = value)" />
            <Button
                v-if="currentView === 'orders' && shownOrderIds.length"
                :label="isEveryOrderOpen ? ctrans('Hide all products') : ctrans('Show all products')"
                type="tertiary"
                :icon="isEveryOrderOpen ? 'fal fa-chevron-down' : 'fal fa-chevron-right'"
                size="s"
                class="ml-auto"
                @click="toggleEveryOrder" />
            <Button
                v-if="canSend && agreedCards.length"
                :label="ctrans('Submit all agreed (:count)', { count: agreedCards.length })"
                type="tertiary"
                icon="fal fa-paper-plane"
                :loading="isSending"
                @click="send(agreedCards.map((card) => card.supplier_id))"
            />
        </div>

        <div v-if="currentView === 'cards'" class="grid grid-cols-1 gap-4 lg:grid-cols-2 2xl:grid-cols-3">
            <section v-for="card in shownCards" :key="card.supplier_id" class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="h-1 w-full" :class="card.all_ready ? 'bg-green-500' : 'bg-amber-400'" />
                <div class="flex items-start justify-between gap-3 px-4 pt-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900">{{ card.supplier_code }}</div>
                        <div class="truncate text-xs text-gray-500" :title="card.supplier_name">{{ card.supplier_name }}</div>
                        <Link v-if="ownOrder(card)" :href="ownOrder(card).url" class="block truncate text-xs text-gray-500 underline decoration-dotted underline-offset-2">{{ ownOrder(card).reference }}</Link>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums"
                        :class="card.all_ready ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                    >
                        <FontAwesomeIcon :icon="card.all_ready ? faCheck : faHourglassHalf" fixed-width aria-hidden="true" />
                        {{ ctrans(":ready of :total ready", { ready: card.ready_count, total: card.parts.length }) }}
                    </span>
                </div>

                <dl class="mt-3 grid grid-cols-3 items-stretch gap-2 px-4 text-center">
                    <div
                        role="button"
                        tabindex="0"
                        v-tooltip="ctrans('See all the products')"
                        class="flex cursor-pointer flex-col rounded-lg bg-gray-50 px-2 py-1.5 outline-none hover:bg-gray-100 focus-visible:ring-1 focus-visible:ring-gray-400"
                        @click="openPreview(card)"
                        @keydown.enter="openPreview(card)"
                        @keydown.space.prevent="openPreview(card)">
                        <dd class="text-lg font-semibold tabular-nums text-gray-900 underline decoration-dotted underline-offset-4">{{ locale.number(card.lines) }}</dd>
                        <dt class="text-[11px] text-gray-500">
                            {{ ctrans("products") }}
                            <span v-if="ourPart(card) && card.parts.length > 1" class="tabular-nums">· {{ ctrans(":count from :organisation", { count: locale.number(ourPart(card).lines), organisation: ourPart(card).organisation_code }) }}</span>
                        </dt>
                    </div>
                    <div class="flex flex-col rounded-lg bg-gray-50 px-2 py-1.5">
                        <dd class="text-lg font-semibold tabular-nums text-gray-900">
                            <div>{{ orgMoney(card, card.amount) ?? money(card, card.amount) }}</div>
                            <div v-if="card.org_exchange" class="text-xs font-normal leading-tight text-gray-400">{{ money(card, card.amount) }}</div>
                        </dd>
                        <dt class="sr-only">{{ ctrans("total") }}</dt>
                    </div>
                    <div class="flex flex-col rounded-lg bg-gray-50 px-2 py-1.5">
                        <dd class="text-lg font-semibold tabular-nums text-gray-900">
                            {{ volume(card.cbm) }}
                            <span v-if="card.lines_without_cbm" v-tooltip="ctrans(':count products have no carton volume recorded and are not counted', { count: card.lines_without_cbm })" class="cursor-help text-sm text-amber-600">
                                <FontAwesomeIcon :icon="faExclamationTriangle" aria-hidden="true" />
                            </span>
                        </dd>
                        <dt v-tooltip="container.label" class="whitespace-nowrap text-[11px] text-gray-500">{{ ctrans(":share of :container", { share: containerShare(card.cbm), container: container.short }) }}</dt>
                    </div>
                </dl>

                <table class="mt-3 w-full text-sm">
                    <thead class="whitespace-nowrap border-y border-gray-100 bg-gray-50 text-left text-[11px] font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="py-1.5 pl-4 pr-2">{{ ctrans("Organisation") }}</th>
                            <th class="px-2 py-1.5 text-right">{{ ctrans("Products") }}</th>
                            <th class="px-2 py-1.5 text-right">{{ ctrans("Total") }}</th>
                            <th class="px-2 py-1.5 text-right">{{ ctrans("Volume") }}</th>
                            <th class="py-1.5 pl-2 pr-4"><span class="sr-only">{{ ctrans("OK to submit") }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="part in card.parts" :key="part.purchase_order_id" :class="{ 'bg-gray-100': part.is_ours }">
                            <td class="py-2 pl-4 pr-2">
                                <div v-tooltip="part.organisation_name" class="font-medium text-gray-800">{{ part.organisation_code }}</div>
                                <Link v-if="part.url" :href="part.url" :title="part.reference" class="block max-w-[9rem] truncate text-[11px] leading-tight text-gray-500 underline decoration-dotted underline-offset-2">{{ part.reference }}</Link>
                                <div v-else :title="part.reference" class="max-w-[9rem] truncate text-[11px] leading-tight text-gray-400">{{ part.reference }}</div>
                            </td>
                            <td class="px-2 py-2 text-right tabular-nums">
                                <button
                                    type="button"
                                    v-tooltip="ctrans('See the products')"
                                    class="rounded px-1 underline decoration-dotted underline-offset-2 hover:bg-gray-200"
                                    @click="openPreview(card, part)">
                                    {{ locale.number(part.lines) }}
                                </button>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                                <div>{{ orgMoney(card, part.amount) ?? money(card, part.amount) }}</div>
                                <div v-if="card.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(card, part.amount) }}</div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                                <div>{{ volume(part.cbm) }}</div>
                                <div v-if="part.cbm" v-tooltip="ctrans(':share of a :container', { share: containerShare(part.cbm), container: container.label })" class="text-[11px] font-normal leading-tight text-gray-400">{{ containerShare(part.cbm) }}</div>
                            </td>
                            <td class="py-2 pl-2 pr-4">
                                <AgentDraftReadyCell :part="part" :canSend="canSend" @setReady="(ready: boolean) => setReady(part, ready)" />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="canSend && isOnlyOurs(card)" class="mx-4 mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">
                    <span v-if="card.others_asked">
                        {{ ctrans(":user told the other organisations on :date. Nobody has added anything, so it is yours to submit.", { user: card.others_asked.by ?? "", date: useFormatTime(card.others_asked.at, { formatTime: "d MMM HH:mm" }) }) }}
                    </span>
                    <template v-else>
                        <span>{{ ctrans("Only your organisation is ordering from this supplier. Want to let the others know before you submit?") }}</span>
                        <Button :label="ctrans('Let them know')" type="tertiary" size="xs" @click="tellOthers(card)" />
                    </template>
                </div>

                <AgentDraftSendActions
                    v-if="canSend"
                    class="mt-auto border-t border-gray-100 px-4 py-3"
                    :card="card"
                    :isSending="isSending"
                    @send="send([card.supplier_id])"
                    @override="openOverride(card)" />
            </section>
        </div>

        <div v-else-if="shownCards.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="whitespace-nowrap border-b border-gray-200 bg-gray-50 text-left text-[11px] font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">{{ ctrans("Purchase order") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Products") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Cartons") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Unit cost") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Total") }}</th>
                        <th class="px-2 py-2 text-right">{{ ctrans("Volume") }}</th>
                        <th class="py-2 pl-2 pr-4"><span class="sr-only">{{ ctrans("OK to submit") }}</span></th>
                    </tr>
                </thead>
                <tbody v-for="card in shownCards" :key="card.supplier_id" class="border-b border-gray-200 last:border-b-0">
                    <tr class="bg-gray-50">
                        <td class="px-4 py-2" colspan="4">
                            <span class="font-semibold text-gray-900">{{ card.supplier_code }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ card.supplier_name }}</span>
                            <Link v-if="ownOrder(card)" :href="ownOrder(card).url" class="ml-2 text-xs text-gray-500 underline decoration-dotted underline-offset-2">{{ ownOrder(card).reference }}</Link>
                            <span class="ml-2 rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums" :class="card.all_ready ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                                {{ ctrans(":ready of :total ready", { ready: card.ready_count, total: card.parts.length }) }}
                            </span>
                            <span class="ml-2 text-xs tabular-nums text-gray-500">{{ ctrans(":count products", { count: locale.number(card.lines) }) }}</span>
                        </td>
                        <td class="px-2 py-2 text-right font-semibold tabular-nums text-gray-900">
                            <div>{{ orgMoney(card, card.amount) ?? money(card, card.amount) }}</div>
                            <div v-if="card.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(card, card.amount) }}</div>
                        </td>
                        <td class="px-2 py-2 text-right font-semibold tabular-nums text-gray-900">
                            <div>{{ volume(card.cbm) }}</div>
                            <div v-if="card.cbm" v-tooltip="ctrans(':share of a :container', { share: containerShare(card.cbm), container: container.label })" class="text-[11px] font-normal leading-tight text-gray-400">{{ containerShare(card.cbm) }}</div>
                        </td>
                        <td class="py-2 pl-2 pr-4">
                            <AgentDraftSendActions v-if="canSend" :card="card" :isSending="isSending" @send="send([card.supplier_id])" @override="openOverride(card)" />
                        </td>
                    </tr>
                    <template v-for="part in card.parts" :key="part.purchase_order_id">
                        <tr class="border-t border-gray-100" :class="{ 'bg-gray-100': part.is_ours }">
                            <td class="py-2 pl-4 pr-2">
                                <div class="flex items-start gap-2">
                                    <button
                                        type="button"
                                        class="mt-0.5 rounded p-0.5 text-gray-400 hover:bg-gray-200 hover:text-gray-700"
                                        :aria-expanded="openOrders.has(part.purchase_order_id)"
                                        :aria-label="openOrders.has(part.purchase_order_id) ? ctrans('Hide the products') : ctrans('Show the products')"
                                        @click="toggleOrder(part.purchase_order_id)">
                                        <FontAwesomeIcon :icon="openOrders.has(part.purchase_order_id) ? faChevronDown : faChevronRight" fixed-width aria-hidden="true" />
                                    </button>
                                    <div class="min-w-0">
                                        <div v-tooltip="part.organisation_name" class="font-medium text-gray-800">{{ part.organisation_code }}</div>
                                        <Link v-if="part.url" :href="part.url" :title="part.reference" class="block max-w-xs truncate text-[11px] leading-tight text-gray-500 underline decoration-dotted underline-offset-2">{{ part.reference }}</Link>
                                        <div v-else :title="part.reference" class="max-w-xs truncate text-[11px] leading-tight text-gray-400">{{ part.reference }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-2 text-right tabular-nums">{{ locale.number(part.lines) }}</td>
                            <td colspan="2" />
                            <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                                <div>{{ orgMoney(card, part.amount) ?? money(card, part.amount) }}</div>
                                <div v-if="card.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(card, part.amount) }}</div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                                <div>{{ volume(part.cbm) }}</div>
                                <div v-if="part.cbm" v-tooltip="ctrans(':share of a :container', { share: containerShare(part.cbm), container: container.label })" class="text-[11px] font-normal leading-tight text-gray-400">{{ containerShare(part.cbm) }}</div>
                            </td>
                            <td class="py-2 pl-2 pr-4">
                                <AgentDraftReadyCell :part="part" :canSend="canSend" @setReady="(ready: boolean) => setReady(part, ready)" />
                            </td>
                        </tr>
                        <template v-if="openOrders.has(part.purchase_order_id)">
                            <tr v-for="line in lines?.[part.purchase_order_id] ?? []" :key="line.id" class="border-t border-gray-50 text-gray-600">
                                <td class="py-1.5 pl-12 pr-2" colspan="2">
                                    <span class="font-medium text-gray-700">{{ line.code }}</span>
                                    <span class="ml-2">{{ line.name }}</span>
                                </td>
                                <td class="px-2 py-1.5 text-right tabular-nums">{{ line.cartons === null ? "—" : locale.number(line.cartons) }}</td>
                                <td class="px-2 py-1.5 text-right tabular-nums">{{ line.unit_cost === null ? "—" : money(card, line.unit_cost) }}</td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">
                                    <div>{{ orgMoney(card, line.amount) ?? money(card, line.amount) }}</div>
                                    <div v-if="card.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(card, line.amount) }}</div>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">{{ volume(line.cbm) }}</td>
                                <td />
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>

        <p v-if="!shownCards.length" class="py-16 text-center text-sm text-gray-400">
            {{ cards.length ? ctrans("Your organisation has no draft purchase orders for :agent", { agent: agentName }) : ctrans("No draft purchase orders for :agent", { agent: agentName }) }}
        </p>
        <p class="mt-8 max-w-3xl text-xs text-gray-400">
            {{ ctrans("Draft purchase orders through :agent, one card per supplier, with what each organisation has stacked on it. :agent sees nothing until a card is submitted. Tick your part when it is finished; a card can be submitted once every organisation has ticked.", { agent: agentName }) }}
        </p>
    </div>

    <Dialog
        v-model:visible="isPreviewOpen"
        modal
        :header="!previewed ? '' : previewed.part ? ctrans(':supplier · :organisation · :reference', { supplier: previewed.card.supplier_code, organisation: previewed.part.organisation_code, reference: previewed.part.reference }) : ctrans(':supplier · every organisation', { supplier: previewed.card.supplier_code })"
        class="w-full max-w-3xl">
        <div v-if="isLoadingPreview" class="space-y-2 py-2">
            <div v-for="row in 5" :key="row" class="flex animate-pulse items-center gap-2">
                <div class="h-8 w-8 rounded bg-gray-100" />
                <div class="h-3 flex-1 rounded bg-gray-100" />
                <div class="h-3 w-24 rounded bg-gray-100" />
            </div>
        </div>
        <p v-else-if="previewFailed" class="py-8 text-center text-sm text-red-600">{{ ctrans("The products could not be loaded, please try again") }}</p>
        <p v-else-if="previewed && !previewLines.length" class="py-8 text-center text-sm text-gray-400">{{ ctrans("No products on this purchase order") }}</p>
        <div v-else-if="previewed" class="max-h-[65vh] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 whitespace-nowrap border-b border-gray-200 bg-gray-50 text-left text-[11px] font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-2 py-1.5">{{ ctrans("Product") }}</th>
                        <th class="px-2 py-1.5 text-right">{{ ctrans("Cartons") }}</th>
                        <th class="px-2 py-1.5 text-right">{{ ctrans("Total") }}</th>
                        <th class="px-2 py-1.5 text-right">{{ ctrans("Volume") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="line in previewLines" :key="line.key">
                        <td class="px-2 py-1.5">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 shrink-0 overflow-hidden rounded bg-gray-100">
                                    <Image v-if="line.image" :src="line.image" :alt="line.code" imageCover class="h-full w-full" />
                                </div>
                                <div class="min-w-0">
                                    <span class="whitespace-nowrap font-medium text-gray-800">{{ line.code }}</span>
                                    <span class="ml-2 text-gray-600">{{ line.name }}</span>
                                    <div v-if="line.organisations.length" class="text-[11px] leading-tight text-gray-400">{{ line.organisations.join(" · ") }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">
                            <div>{{ line.cartons === null ? "—" : locale.number(line.cartons) }}</div>
                            <div v-if="line.skos !== null" class="text-[11px] leading-tight text-gray-400">{{ ctrans(":count SKOs", { count: locale.number(line.skos) }) }}</div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">
                            <div>{{ orgMoney(previewed.card, line.amount) ?? money(previewed.card, line.amount) }}</div>
                            <div v-if="previewed.card.org_exchange" class="text-[11px] leading-tight text-gray-400">{{ money(previewed.card, line.amount) }}</div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-right tabular-nums">{{ volume(line.cbm) }}</td>
                    </tr>
                </tbody>
                <tfoot class="border-t border-gray-200 font-semibold text-gray-900">
                    <tr>
                        <td class="px-2 py-2" colspan="2">{{ ctrans(":count products", { count: locale.number(previewTotals.lines) }) }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">
                            <div>{{ orgMoney(previewed.card, previewTotals.amount) ?? money(previewed.card, previewTotals.amount) }}</div>
                            <div v-if="previewed.card.org_exchange" class="text-[11px] font-normal leading-tight text-gray-400">{{ money(previewed.card, previewTotals.amount) }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-right tabular-nums">{{ volume(previewTotals.cbm) }}</td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-3 flex justify-end">
                <Link v-if="previewed.part?.url" :href="previewed.part.url">
                    <Button :label="ctrans('Open the purchase order')" type="tertiary" icon="fal fa-external-link" size="s" />
                </Link>
            </div>
        </div>
    </Dialog>

    <Dialog v-model:visible="isOverrideOpen" modal :header="ctrans('Submit without waiting for the others?')" class="w-full max-w-lg">
        <div v-if="overrideCard" class="space-y-4 text-sm text-gray-700">
            <div class="flex gap-3 rounded-lg border border-red-300 bg-red-50 p-3 text-red-800">
                <FontAwesomeIcon :icon="faExclamationTriangle" class="mt-0.5 text-lg" fixed-width aria-hidden="true" />
                <div>
                    <p class="font-semibold">
                        {{ ctrans(":organisations have not said their order to :supplier is ready.", { organisations: waitingParts(overrideCard).map((part) => part.organisation_name).join(", "), supplier: overrideCard.supplier_code }) }}
                    </p>
                    <p class="mt-1">
                        {{ ctrans("If you submit now, their drafts go with yours as they are, so :agent can start on everything for this supplier. Nobody can add to them afterwards. This cannot be undone from here.", { agent: agentName }) }}
                    </p>
                </div>
            </div>

            <label class="block">
                <span class="font-medium text-gray-800">{{ ctrans("Why are you not waiting for them?") }}</span>
                <Textarea v-model="overrideReason" rows="3" autoResize class="mt-1 w-full" :class="fieldFocusClass" :placeholder="ctrans('It is recorded on every purchase order submitted')" />
                <span class="mt-1 block text-xs text-gray-500">{{ ctrans("At least :count characters", { count: MINIMUM_REASON_LENGTH }) }}</span>
            </label>

            <div class="flex justify-end gap-2">
                <Button :label="ctrans('Keep waiting')" type="tertiary" size="s" @click="overrideCard = null" />
                <Button
                    :label="ctrans('Submit everything now')"
                    type="negative"
                    icon="fal fa-paper-plane"
                    size="s"
                    :disabled="!isReasonGiven"
                    :loading="isSending"
                    @click="send([overrideCard.supplier_id], overrideReason.trim())"
                />
            </div>
        </div>
    </Dialog>
</template>

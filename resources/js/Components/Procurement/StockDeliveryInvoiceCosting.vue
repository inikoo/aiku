<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 28 Sep 2026, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue"
import { router, usePoll } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Checkbox from "primevue/checkbox"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import { faFileInvoiceDollar, faCheck, faTimes, faExchange } from "@fal"
import { faSpinnerThird } from "@fad"
import { faExclamationTriangle, faCheckCircle } from "@fas"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

interface ReviewItem {
    id: number
    code: string | null
    name: string | null
    quantity: number
    expected_amount: number
    current_cost: number
    invoice_quantity: number | null
    invoice_amount: number | null
    quantity_differs: boolean
    amount_differs: boolean
    proposed_cost: number
    needs_check: boolean
}

interface InvoiceLine {
    code: string | null
    description: string | null
    quantity: number | null
    unit_price: number | null
    amount: number | null
}

interface Reading {
    state: "reading" | "read" | "failed"
    error?: string
    read_at?: string
    applied_at?: string | null
    is_invoice?: boolean
    invoice_number?: string | null
    invoice_date?: string | null
    currency?: string | null
    total?: number | null
    charges?: { label: string; amount: number }[]
    delivery_currency?: string | null
    currency_mismatch?: boolean
    currency_unknown?: boolean
    exchange?: number | null
    follow_blockers?: string[]
    total_mismatch?: boolean
    lines_total?: number
    charges_total?: number
    can_apply?: boolean
    unmatched?: InvoiceLine[]
    items?: ReviewItem[]
}

interface Invoice {
    media_id: number
    name: string
    scope: string
    readRoute: routeType
    applyRoute: routeType
    reading: Reading | null
}

interface Draft {
    invoice_number: string
    invoice_date: string
    invoice_total: string
    costs: Record<number, string>
    charges: Record<number, boolean>
    assignments: Record<number, number | "not_in_delivery" | "leave_out">
}

const props = defineProps<{
    invoices: Invoice[]
    canEdit: boolean
}>()

const locale = useLocaleStore()
const drafts = reactive<Record<string, Draft>>({})
const openInvoice = ref<number | null>(null)
const busy = ref<number | null>(null)
const choosing = ref<Invoice | null>(null)
const exchange = ref("")

const draftKey = (invoice: Invoice) => `${invoice.media_id}-${invoice.reading?.read_at}`

const money = (amount: number | null | undefined, currency?: string | null) =>
    amount == null ? "—" : currency ? locale.currencyFormat(currency, amount) : locale.number(amount)

watch(
    () => props.invoices,
    (invoices) => {
        invoices.forEach((invoice) => {
            const reading = invoice.reading
            if (reading?.state !== "read" || drafts[draftKey(invoice)]) {
                return
            }

            drafts[draftKey(invoice)] = {
                invoice_number: reading.invoice_number ?? "",
                invoice_date: reading.invoice_date ?? "",
                invoice_total: reading.total == null ? "" : String(reading.total),
                costs: Object.fromEntries((reading.items ?? []).map((item) => [item.id, String(item.proposed_cost)])),
                charges: Object.fromEntries((reading.charges ?? []).map((charge, index) => [index, charge.amount > 0])),
                assignments: Object.fromEntries((reading.unmatched ?? []).map((_, index) => [index, "not_in_delivery"])),
            }
        })
    },
    { immediate: true, deep: true }
)

const isReading = computed(() => props.invoices.some((invoice) => invoice.reading?.state === "reading"))

const { start: startPolling, stop: stopPolling } = usePoll(3000, { only: ["invoice_costing"] }, { autoStart: false })

watch(isReading, (reading) => (reading ? startPolling() : stopPolling()), { immediate: true })

const proposedCost = (invoice: Invoice, item: ReviewItem): number => {
    const draft = drafts[draftKey(invoice)]
    const assigned = (invoice.reading?.unmatched ?? []).reduce(
        (sum, line, index) => sum + (draft.assignments[index] === item.id ? Number(line.amount ?? 0) : 0),
        0
    )

    return Number(draft.costs[item.id] || 0) + assigned
}

const read = (invoice: Invoice) => {
    router.post(route(invoice.readRoute.name, invoice.readRoute.parameters), {}, {
        preserveScroll: true,
        onStart: () => (busy.value = invoice.media_id),
        onFinish: () => (busy.value = null),
        onSuccess: () => (openInvoice.value = invoice.media_id),
    })
}

const invoiceCurrency = (reading: Reading) => (reading.currency_mismatch ? reading.currency : reading.delivery_currency)

const assignmentOptions = (reading: Reading) => [
    { label: ctrans("Invoiced but not in the delivery"), value: "not_in_delivery" },
    ...(reading.items ?? []).map((item) => ({ label: `${ctrans("Add to")} ${item.code}`, value: item.id })),
    { label: ctrans("Leave out"), value: "leave_out" },
]

const toDate = (iso: string) => (iso ? new Date(`${iso}T00:00:00`) : null)

const toIso = (date: Date | null) =>
    date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : ""

const startApply = (invoice: Invoice) => {
    if (!invoice.reading?.currency_mismatch) {
        return apply(invoice)
    }

    exchange.value = invoice.reading.exchange ? String(invoice.reading.exchange) : ""
    choosing.value = invoice
}

const choose = (followInvoice: boolean) => {
    const invoice = choosing.value as Invoice

    choosing.value = null
    apply(invoice, followInvoice ? { follow_invoice: true } : { follow_invoice: false, exchange: Number(exchange.value) })
}

const apply = (invoice: Invoice, currencyChoice: { follow_invoice?: boolean; exchange?: number } = {}) => {
    const draft = drafts[draftKey(invoice)]
    const reading = invoice.reading as Reading

    router.post(route(invoice.applyRoute.name, invoice.applyRoute.parameters), {
        invoice_number: draft.invoice_number || null,
        invoice_date: draft.invoice_date,
        invoice_total: Number(draft.invoice_total),
        items: (reading.items ?? []).map((item) => ({ id: item.id, cost: Math.round(proposedCost(invoice, item) * 100) / 100 })),
        charges: (reading.charges ?? []).filter((_, index) => draft.charges[index]),
        not_in_delivery: (reading.unmatched ?? [])
            .filter((line, index) => draft.assignments[index] === "not_in_delivery" && Number(line.amount ?? 0) > 0)
            .map((line) => ({ label: [line.code, line.description].filter(Boolean).join(" ").slice(0, 255) || ctrans("Invoice line"), amount: line.amount })),
        ...currencyChoice,
    }, {
        preserveScroll: true,
        onStart: () => (busy.value = invoice.media_id),
        onFinish: () => (busy.value = null),
        onError: (errors) => notify({ title: ctrans("The costs were not applied"), text: Object.values(errors).join(" "), type: "error" }),
    })
}
</script>

<template>
    <div class="border-b border-gray-300 px-4 py-3 text-sm text-gray-600">
        <div class="mb-2 flex items-center gap-2">
            <FontAwesomeIcon :icon="faFileInvoiceDollar" class="text-gray-400" fixed-width />
            <span class="font-medium">{{ ctrans("Costs from the supplier invoice") }}</span>
        </div>

        <p v-if="!invoices.length" class="text-gray-500">
            {{ ctrans("Attach the supplier's invoice as Invoice or Proforma to read its costs.") }}
        </p>

        <div v-for="invoice in invoices" :key="invoice.media_id" class="mb-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-medium text-gray-800">{{ invoice.name }}</span>
                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{{ invoice.scope }}</span>

                <span v-if="invoice.reading?.state === 'reading'" class="flex items-center gap-1 text-gray-500">
                    <FontAwesomeIcon :icon="faSpinnerThird" spin fixed-width />{{ ctrans("Reading…") }}
                </span>
                <span v-else-if="invoice.reading?.state === 'failed'" class="text-red-600">{{ invoice.reading.error }}</span>
                <span v-else-if="invoice.reading?.applied_at" class="flex items-center gap-1 text-green-600">
                    <FontAwesomeIcon :icon="faCheckCircle" fixed-width />{{ ctrans("In the costing") }}
                </span>

                <Button v-if="canEdit && invoice.reading?.state !== 'reading' && !invoice.reading?.applied_at"
                    :label="invoice.reading?.state === 'read' ? ctrans('Read again') : ctrans('Read invoice')"
                    type="tertiary" size="xs" :loading="busy === invoice.media_id" @click="read(invoice)" />
                <Button v-if="invoice.reading?.state === 'read'"
                    :label="openInvoice === invoice.media_id ? ctrans('Hide') : ctrans('Review invoice')"
                    type="tertiary" size="xs" @click="openInvoice = openInvoice === invoice.media_id ? null : invoice.media_id" />
            </div>

            <div v-if="invoice.reading?.state === 'read' && openInvoice === invoice.media_id && drafts[draftKey(invoice)]" class="mt-2 space-y-3 rounded border border-gray-200 p-3">
                <div v-if="invoice.reading.is_invoice === false || invoice.reading.currency_mismatch || invoice.reading.total_mismatch" class="space-y-1 text-orange-600">
                    <div v-if="invoice.reading.is_invoice === false"><FontAwesomeIcon :icon="faExclamationTriangle" fixed-width /> {{ ctrans("This does not look like an invoice.") }}</div>
                    <div v-if="invoice.reading.currency_mismatch">
                        <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width />
                        {{ invoice.reading.currency_unknown
                            ? ctrans("The invoice is in :invoice, a currency we do not know: enter its costs by hand.", { invoice: invoice.reading.currency ?? "" })
                            : ctrans("The invoice is in :invoice and the delivery in :delivery: when you apply it you choose whether the delivery follows the invoice.", { invoice: invoice.reading.currency ?? "", delivery: invoice.reading.delivery_currency ?? "" }) }}
                    </div>
                    <div v-if="invoice.reading.total_mismatch">
                        <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width />
                        {{ ctrans("Lines and charges add up to :sum, the invoice total says :total.", { sum: String(money((invoice.reading.lines_total ?? 0) + (invoice.reading.charges_total ?? 0), invoice.reading.currency)), total: String(money(invoice.reading.total, invoice.reading.currency)) }) }}
                    </div>
                </div>

                <div v-if="invoice.reading.items?.some((item) => item.needs_check)" class="text-orange-600">
                    <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width />
                    {{ ctrans("Some goods costs are far from the order amount, check them before applying: the invoice may count packs or cartons rather than units.") }}
                </div>

                <div class="flex flex-wrap gap-3">
                    <label class="flex items-center gap-1">{{ ctrans("Number") }}
                        <InputText v-model="drafts[draftKey(invoice)].invoice_number" size="small" class="w-36" />
                    </label>
                    <label class="flex items-center gap-1">{{ ctrans("Date") }}
                        <DatePicker :modelValue="toDate(drafts[draftKey(invoice)].invoice_date)" dateFormat="dd/mm/yy" size="small" class="w-36"
                            @update:modelValue="(date) => (drafts[draftKey(invoice)].invoice_date = toIso(date as Date | null))" />
                    </label>
                    <label class="flex items-center gap-1">{{ ctrans("Total") }}
                        <InputText v-model="drafts[draftKey(invoice)].invoice_total" type="number" size="small" min="0" step="0.01" class="w-32 tabular-nums" />
                        <span class="text-gray-400">{{ invoiceCurrency(invoice.reading) }}</span>
                    </label>
                </div>

                <table class="w-full text-left text-xs">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="py-1">{{ ctrans("Product") }}</th>
                            <th class="py-1 text-right">{{ ctrans("Received") }}</th>
                            <th class="py-1 text-right">{{ ctrans("Invoiced") }}</th>
                            <th class="py-1 text-right">{{ ctrans("Order amount") }}</th>
                            <th class="py-1 text-right">{{ ctrans("Invoice amount") }}</th>
                            <th class="py-1 text-right">{{ ctrans("Goods cost") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in invoice.reading.items" :key="item.id" class="border-t border-gray-100" :class="item.needs_check ? 'bg-orange-50' : ''">
                            <td class="py-1"><span class="font-medium">{{ item.code }}</span> {{ item.name }}</td>
                            <td class="py-1 text-right">{{ locale.number(item.quantity) }}</td>
                            <td class="py-1 text-right" :class="item.quantity_differs ? 'font-medium text-orange-600' : ''">
                                {{ item.invoice_quantity == null ? ctrans("not on invoice") : locale.number(item.invoice_quantity) }}
                            </td>
                            <td class="py-1 text-right">{{ money(item.expected_amount, invoice.reading.delivery_currency) }}</td>
                            <td class="py-1 text-right" :class="item.amount_differs ? 'font-medium text-orange-600' : ''">{{ money(item.invoice_amount, invoiceCurrency(invoice.reading)) }}</td>
                            <td class="py-1 text-right">
                                <InputText v-model="drafts[draftKey(invoice)].costs[item.id]" type="number" size="small" min="0" step="0.01" class="w-28 text-right tabular-nums" />
                                <div v-if="proposedCost(invoice, item) !== Number(drafts[draftKey(invoice)].costs[item.id] || 0)" class="text-gray-500">
                                    = {{ money(proposedCost(invoice, item), invoiceCurrency(invoice.reading)) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="invoice.reading.unmatched?.length">
                    <div class="mb-1 font-medium text-orange-600">{{ ctrans("Invoice lines not matched to a product of this delivery") }}</div>
                    <div v-for="(line, index) in invoice.reading.unmatched" :key="index" class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-medium">{{ line.code ?? "—" }}</span>
                        <span>{{ line.description }}</span>
                        <span>{{ line.quantity == null ? "" : "× " + locale.number(line.quantity) }}</span>
                        <span class="font-medium">{{ money(line.amount, invoiceCurrency(invoice.reading)) }}</span>
                        <Select v-if="line.amount != null" v-model="drafts[draftKey(invoice)].assignments[index]" :options="assignmentOptions(invoice.reading)"
                            optionLabel="label" optionValue="value" size="small" class="w-72" />
                    </div>
                </div>

                <div v-if="invoice.reading.charges?.length">
                    <div class="mb-1 font-medium">{{ ctrans("Other charges on the invoice") }}</div>
                    <label v-for="(charge, index) in invoice.reading.charges" :key="index" class="flex items-center gap-2 text-xs">
                        <Checkbox v-model="drafts[draftKey(invoice)].charges[index]" binary :disabled="charge.amount <= 0" />
                        <span>{{ charge.label }}</span>
                        <span class="font-medium">{{ money(charge.amount, invoiceCurrency(invoice.reading)) }}</span>
                        <span v-if="charge.amount > 0" class="text-gray-400">{{ ctrans("add as extra expense") }}</span>
                        <span v-else class="text-gray-400">{{ ctrans("discounts go into the goods cost of the lines") }}</span>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <Button v-if="canEdit" :label="ctrans('Apply to costing')" type="tertiary" size="xs"
                        :disabled="!invoice.reading.can_apply || invoice.reading.currency_unknown || !drafts[draftKey(invoice)].invoice_date || drafts[draftKey(invoice)].invoice_total === ''"
                        :loading="busy === invoice.media_id" @click="startApply(invoice)">
                        <template #icon>
                            <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <span v-if="!invoice.reading.can_apply" class="text-xs text-gray-500">{{ ctrans("Costs can be applied once the costing has started and until it is done.") }}</span>
                </div>
            </div>
        </div>

        <Dialog :visible="!!choosing" modal :header="ctrans('Should the stock delivery follow the invoice?')" :style="{ width: '34rem', maxWidth: 'calc(100vw - 2rem)' }" @update:visible="choosing = null">
            <div v-if="choosing?.reading" class="space-y-4 text-sm text-gray-600">
                <p>{{ ctrans("The invoice is in :invoice and the stock delivery in :delivery.", { invoice: choosing.reading.currency ?? "", delivery: choosing.reading.delivery_currency ?? "" }) }}</p>

                <div>
                    <div class="font-medium text-gray-800">{{ ctrans("Yes, follow the invoice") }}</div>
                    <p>{{ ctrans("The stock delivery changes to :invoice and takes the costs exactly as invoiced. Its purchase orders stay in :delivery.", { invoice: choosing.reading.currency ?? "", delivery: choosing.reading.delivery_currency ?? "" }) }}</p>
                    <p v-if="choosing.reading.follow_blockers?.length" class="mt-1 text-orange-600">
                        <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
                        {{ ctrans("Not possible on this delivery:") }} {{ choosing.reading.follow_blockers.join(" ") }}
                    </p>
                </div>

                <div>
                    <div class="font-medium text-gray-800">{{ ctrans("No, keep :delivery", { delivery: choosing.reading.delivery_currency ?? "" }) }}</div>
                    <p>{{ ctrans("The stock delivery stays in :delivery. The invoice is recorded in :invoice and its costs are converted at this rate.", { invoice: choosing.reading.currency ?? "", delivery: choosing.reading.delivery_currency ?? "" }) }}</p>
                    <label class="mt-2 flex items-center gap-2">
                        <span>1 {{ choosing.reading.currency }} =</span>
                        <InputText v-model="exchange" type="number" size="small" min="0" step="0.000001" class="w-32 tabular-nums" />
                        <span>{{ choosing.reading.delivery_currency }}</span>
                    </label>
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <Button type="tertiary" :label="ctrans('Cancel')" @click="choosing = null">
                        <template #icon>
                            <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('No, keep :delivery', { delivery: choosing.reading.delivery_currency ?? '' })" :disabled="!(Number(exchange) > 0)" @click="choose(false)">
                        <template #icon>
                            <FontAwesomeIcon :icon="faExchange" class="text-amber-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('Yes, follow the invoice')" :disabled="!!choosing.reading.follow_blockers?.length" @click="choose(true)">
                        <template #icon>
                            <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>

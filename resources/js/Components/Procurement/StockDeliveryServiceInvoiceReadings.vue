<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue"
import { router, usePoll } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Select from "primevue/select"
import Checkbox from "primevue/checkbox"
import FileUpload from "primevue/fileupload"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faFileSearch, faCheck, faTimes, faRedo, faExclamationTriangle, faCheckCircle, faPaperclip } from "@fal"
import { faSpinnerThird } from "@fad"
import Button from "@/Components/Elements/Buttons/Button.vue"
import StockDeliveryHandTypedCostWarning, { type HandTypedCost } from "@/Components/Procurement/StockDeliveryHandTypedCostWarning.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"

library.add(faFileSearch, faCheck, faTimes, faRedo, faExclamationTriangle, faCheckCircle, faPaperclip, faSpinnerThird)

interface ReadingLine {
	description: string | null
	type: string | null
	amount: number | null
}

interface Reading {
	state: "reading" | "read" | "failed"
	error?: string
	read_at?: string
	applied_at?: string | null
	issuer?: string | null
	invoice_number?: string | null
	invoice_date?: string | null
	currency?: string | null
	org_exchange?: number | null
	lines?: ReadingLine[]
	lines_total?: number
	net_total?: number | null
	vat_total?: number | null
	total_payable?: number | null
	problems?: string[]
	needs_manual_check?: boolean
	can_apply?: boolean
}

export interface ServiceInvoiceReading {
	media_id: number
	ulid: string
	name: string
	readRoute: routeType
	applyRoute: routeType
	reading: Reading | null
}

const props = defineProps<{
	readings: ServiceInvoiceReading[]
	readRoute: routeType
	types: { label: string; value: string }[]
	handTyped: HandTypedCost[]
	costKinds: Record<string, string | null>
	orgCurrency: string
	canEdit: boolean
}>()

const locale = useLocaleStore()
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent]"
const quietVisit = { preserveScroll: true, preserveState: true, only: ["service_invoices", "costing", "box_stats"] }

const open = ref<number | null>(null)
const busy = ref<number | "upload" | null>(null)
const types = reactive<Record<string, (string | null)[]>>({})
const checked = reactive<Record<string, boolean>>({})

const draftKey = (row: ServiceInvoiceReading) => `${row.media_id}-${row.reading?.read_at}`
const money = (amount: number | null | undefined, currency?: string | null) =>
	amount == null ? "—" : currency ? locale.currencyFormat(currency, amount) : locale.number(amount)

watch(
	() => props.readings,
	(readings) =>
		readings.forEach((row) => {
			if (row.reading?.state === "read" && !types[draftKey(row)]) {
				types[draftKey(row)] = (row.reading.lines ?? []).map((line) => line.type)
			}
		}),
	{ immediate: true, deep: true }
)

const isReading = computed(() => props.readings.some((row) => row.reading?.state === "reading"))
const { start: startPolling, stop: stopPolling } = usePoll(3000, { only: ["service_invoices"] }, { autoStart: false })
watch(isReading, (reading) => (reading ? startPolling() : stopPolling()), { immediate: true })

const onError = (errors: Record<string, string>) =>
	notify({ title: ctrans("Something went wrong"), text: Object.values(errors).join(" "), type: "error" })

const upload = (event: { files: File[] }) =>
	router.post(route(props.readRoute.name, props.readRoute.parameters), { attachments: event.files }, {
		...quietVisit,
		forceFormData: true,
		onStart: () => (busy.value = "upload"),
		onFinish: () => (busy.value = null),
		onError,
	})

const read = (row: ServiceInvoiceReading) =>
	router.post(route(row.readRoute.name, row.readRoute.parameters), {}, {
		...quietVisit,
		onStart: () => (busy.value = row.media_id),
		onFinish: () => (busy.value = null),
		onError,
	})

const billed = (row: ServiceInvoiceReading) => {
	const exchange = row.reading?.org_exchange ?? null
	const totals: Record<string, number | null> = {}

	;(row.reading?.lines ?? []).forEach((line, index) => {
		const kind = props.costKinds[types[draftKey(row)]?.[index] ?? ""]
		if (kind) {
			totals[kind] = exchange === null ? null : Math.round(((totals[kind] ?? 0) + Number(line.amount ?? 0) * exchange) * 100) / 100
		}
	})

	return totals
}

const canApply = (row: ServiceInvoiceReading) =>
	Boolean(row.reading?.can_apply)
	&& (types[draftKey(row)] ?? []).every((type) => type)
	&& (!row.reading?.needs_manual_check || checked[draftKey(row)])

const apply = (row: ServiceInvoiceReading) =>
	router.post(route(row.applyRoute.name, row.applyRoute.parameters), { read_at: row.reading?.read_at, types: types[draftKey(row)], checked: Boolean(checked[draftKey(row)]) }, {
		...quietVisit,
		onStart: () => (busy.value = row.media_id),
		onFinish: () => (busy.value = null),
		onSuccess: () => {
			open.value = null
			notify({ title: ctrans("Service invoice added"), type: "success" })
		},
		onError,
	})
</script>

<template>
	<div class="space-y-2 border-t border-gray-200 pt-3 text-sm">
		<div class="flex items-center justify-between gap-2">
			<span class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ctrans("Read from a document") }}</span>
			<FileUpload
				v-if="canEdit"
				mode="basic"
				auto
				customUpload
				multiple
				accept=".pdf,.xlsx,.xls,.csv,image/*"
				:maxFileSize="50000000"
				:disabled="busy === 'upload'"
				:chooseLabel="ctrans('Read an invoice')"
				:pt="{ pcChooseButton: { root: { class: '!border !border-gray-300 !bg-white !px-2 !py-1 !text-xs !text-gray-700 hover:!bg-gray-50' } } }"
				@uploader="upload"
			>
				<template #chooseicon>
					<FontAwesomeIcon icon="fal fa-file-search" class="text-gray-500" fixed-width aria-hidden="true" />
				</template>
			</FileUpload>
		</div>

		<p v-if="!readings.length" class="text-gray-500">
			{{ ctrans("Upload the invoice of the forwarder, customs broker or haulier and it is read for you to check. Nothing is costed until you add it.") }}
		</p>

		<div v-for="row in readings" :key="row.media_id">
			<div class="flex flex-wrap items-center gap-2">
				<a :href="route('grp.media.download', { id: row.ulid })" class="min-w-0 truncate text-gray-700 hover:underline">
					<FontAwesomeIcon icon="fal fa-paperclip" class="text-gray-400" fixed-width aria-hidden="true" />
					{{ row.name }}
				</a>

				<span v-if="row.reading?.state === 'reading'" class="flex items-center gap-1 text-gray-500">
					<FontAwesomeIcon :icon="faSpinnerThird" spin fixed-width aria-hidden="true" />{{ ctrans("Reading…") }}
				</span>
				<span v-else-if="row.reading?.state === 'failed'" class="text-red-600">{{ row.reading.error }}</span>
				<span v-else-if="row.reading?.applied_at" class="flex items-center gap-1 text-green-700">
					<FontAwesomeIcon icon="fal fa-check-circle" fixed-width aria-hidden="true" />{{ ctrans("In the service invoices") }}
				</span>
				<span v-else-if="row.reading?.state === 'read' && row.reading.problems?.length" class="flex items-center gap-1 text-amber-700">
					<FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />{{ ctrans("Enter it by hand") }}
				</span>

				<span class="ml-auto flex items-center gap-2">
					<Button v-if="canEdit && row.reading?.state !== 'reading' && !row.reading?.applied_at" type="tertiary" size="xs"
						:label="row.reading?.state === 'read' ? ctrans('Read again') : ctrans('Read')" :loading="busy === row.media_id" @click="read(row)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-redo" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button v-if="row.reading?.state === 'read' && !row.reading.applied_at" type="tertiary" size="xs"
						:label="open === row.media_id ? ctrans('Hide') : ctrans('Review')" @click="open = open === row.media_id ? null : row.media_id">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-file-search" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</span>
			</div>

			<div v-if="row.reading?.state === 'read' && open === row.media_id && types[draftKey(row)]" class="mt-2 space-y-3 rounded border border-gray-200 p-3">
				<ul v-if="row.reading.problems?.length" class="space-y-1 text-amber-700">
					<li v-for="problem in row.reading.problems" :key="problem" class="flex gap-1.5">
						<FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5" fixed-width aria-hidden="true" />
						<span>{{ problem }}</span>
					</li>
					<li class="text-gray-600">{{ ctrans("A reading that fails a check is never corrected here: add this invoice by hand with Add.") }}</li>
				</ul>

				<dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs sm:grid-cols-4">
					<div><dt class="text-gray-500">{{ ctrans("Issued by") }}</dt><dd class="text-gray-800">{{ row.reading.issuer ?? "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Invoice reference") }}</dt><dd class="text-gray-800">{{ row.reading.invoice_number ?? "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Invoice date") }}</dt><dd class="text-gray-800">{{ row.reading.invoice_date ? useFormatTime(row.reading.invoice_date) : "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Currency") }}</dt><dd class="text-gray-800">{{ row.reading.currency ?? "—" }}</dd></div>
				</dl>

				<table class="w-full text-left text-xs">
					<thead class="text-gray-500">
						<tr>
							<th class="py-1 font-medium">{{ ctrans("Line") }}</th>
							<th class="py-1 font-medium">{{ ctrans("Type") }}</th>
							<th class="py-1 text-right font-medium">{{ ctrans("Amount") }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(line, index) in row.reading.lines" :key="index" class="border-t border-gray-100">
							<td class="py-1 pr-2 text-gray-800">{{ line.description ?? "—" }}</td>
							<td class="py-1 pr-2">
								<Select v-model="types[draftKey(row)][index]" :options="props.types" optionLabel="label" optionValue="value" size="small"
									:placeholder="ctrans('The readers do not agree, choose')" :disabled="!canEdit || !row.reading.can_apply"
									:invalid="!types[draftKey(row)][index]" class="w-full" :class="fieldFocusClass" />
							</td>
							<td class="py-1 text-right tabular-nums text-gray-800">{{ money(line.amount, row.reading.currency) }}</td>
						</tr>
					</tbody>
					<tfoot class="border-t border-gray-300 text-gray-800">
						<tr>
							<td colspan="2" class="py-1 text-right text-gray-500">{{ ctrans("Total before VAT") }}</td>
							<td class="py-1 text-right font-medium tabular-nums">{{ money(row.reading.net_total, row.reading.currency) }}</td>
						</tr>
						<tr v-if="row.reading.vat_total">
							<td colspan="2" class="py-1 text-right text-gray-500">{{ ctrans("VAT, not part of the cost") }}</td>
							<td class="py-1 text-right tabular-nums">{{ money(row.reading.vat_total, row.reading.currency) }}</td>
						</tr>
					</tfoot>
				</table>

				<p class="text-xs text-gray-500">
					{{ ctrans("The amounts are the ones printed on the document and cannot be changed here. Lines of the same type are added as one service invoice.") }}
				</p>

				<StockDeliveryHandTypedCostWarning v-if="row.reading.can_apply" :handTyped="handTyped" :billed="billed(row)" :orgCurrency="orgCurrency" />

				<label v-if="row.reading.needs_manual_check && row.reading.can_apply" class="flex items-start gap-2 text-xs text-amber-700">
					<Checkbox v-model="checked[draftKey(row)]" binary :inputId="`service-invoice-reading-checked-${row.media_id}`" />
					<span>{{ ctrans("This document could not be checked automatically. I have checked every amount, and that it is of this delivery, against the document.") }}</span>
				</label>

				<div v-if="canEdit && row.reading.can_apply" class="flex justify-end gap-2">
					<Button type="tertiary" size="xs" :label="ctrans('Cancel')" @click="open = null">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" size="xs" :label="ctrans('Add to the service invoices')" :disabled="!canApply(row)" :loading="busy === row.media_id" @click="apply(row)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</div>
	</div>
</template>

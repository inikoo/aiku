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
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import FileUpload from "primevue/fileupload"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faFileSearch, faCheck, faTimes, faRedo, faExclamationTriangle, faCheckCircle, faPaperclip, faPencil } from "@fal"
import { faSpinnerThird } from "@fad"
import Button from "@/Components/Elements/Buttons/Button.vue"
import StockDeliveryHandTypedCostWarning, { type HandTypedCost } from "@/Components/Procurement/StockDeliveryHandTypedCostWarning.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"

library.add(faFileSearch, faCheck, faTimes, faRedo, faExclamationTriangle, faCheckCircle, faPaperclip, faPencil, faSpinnerThird)

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
	delivery_confirmation?: {
		phrase: string
		reference: string
		printed: string[]
		dates_fit: boolean | null
		left_at: string | null
		arrived_at: string | null
	} | null
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

const emit = defineEmits<{ manual: [] }>()

const locale = useLocaleStore()
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent]"
const quietVisit = { preserveScroll: true, preserveState: true, only: ["service_invoices", "costing", "box_stats"] }

const open = ref<number | null>(null)
const busy = ref<number | "upload" | null>(null)
const types = reactive<Record<string, (string | null)[]>>({})
const checked = reactive<Record<string, boolean>>({})
const dialog = ref(false)
const confirmation = ref("")

const current = computed(() => props.readings.find((row) => row.media_id === open.value) ?? null)

const pending = computed(() => props.readings.filter((row) => !row.reading?.applied_at))

const openDialog = (row: ServiceInvoiceReading | null = null) => {
	open.value = row?.media_id ?? null
	confirmation.value = ""
	dialog.value = true
}

const typeByHand = () => {
	dialog.value = false
	emit("manual")
}

defineExpose({ openDialog })

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

const upload = (event: { files: File[] }) => {
	const known = props.readings.map((row) => row.media_id)

	router.post(route(props.readRoute.name, props.readRoute.parameters), { attachments: event.files }, {
		...quietVisit,
		forceFormData: true,
		onStart: () => (busy.value = "upload"),
		onFinish: () => (busy.value = null),
		onSuccess: () => {
			confirmation.value = ""
			open.value = (props.readings.find((row) => !known.includes(row.media_id)) ?? props.readings[0])?.media_id ?? null
		},
		onError,
	})
}

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

const typed = (text: string) => text.trim().replace(/\s+/g, " ").replace(/\.$/, "").toLowerCase()

const canApply = (row: ServiceInvoiceReading) =>
	Boolean(row.reading?.can_apply)
	&& (types[draftKey(row)] ?? []).every((type) => type)
	&& (!row.reading?.needs_manual_check || checked[draftKey(row)])
	&& (!row.reading?.delivery_confirmation || typed(confirmation.value) === typed(row.reading.delivery_confirmation.phrase))

const apply = (row: ServiceInvoiceReading) =>
	router.post(route(row.applyRoute.name, row.applyRoute.parameters), { read_at: row.reading?.read_at, types: types[draftKey(row)], checked: Boolean(checked[draftKey(row)]), confirmation: confirmation.value || null }, {
		...quietVisit,
		onStart: () => (busy.value = row.media_id),
		onFinish: () => (busy.value = null),
		onSuccess: () => {
			dialog.value = false
			open.value = null
			notify({ title: ctrans("Service invoice added"), type: "success" })
		},
		onError,
	})
</script>

<template>
	<div class="text-sm">
		<ul v-if="pending.length" class="space-y-1">
			<li v-for="row in pending" :key="row.media_id">
				<button type="button" class="flex w-full items-center gap-2 rounded px-1 py-0.5 text-left hover:bg-gray-50" @click="openDialog(row)">
					<FontAwesomeIcon icon="fal fa-paperclip" class="text-gray-400" fixed-width aria-hidden="true" />
					<span class="min-w-0 truncate text-gray-700">{{ row.name }}</span>
					<span v-if="row.reading?.state === 'reading'" class="ml-auto flex shrink-0 items-center gap-1 text-gray-500">
						<FontAwesomeIcon :icon="faSpinnerThird" spin fixed-width aria-hidden="true" />{{ ctrans("Reading…") }}
					</span>
					<span v-else-if="row.reading?.state === 'read' && row.reading.problems?.length" class="ml-auto shrink-0 text-amber-700">{{ ctrans("Enter it by hand") }}</span>
					<span v-else-if="row.reading?.state === 'read'" class="ml-auto shrink-0 text-amber-700">{{ ctrans("Read, review it to add it") }}</span>
					<span v-else class="ml-auto shrink-0 text-red-600">{{ row.reading?.error ?? ctrans("Not read") }}</span>
				</button>
			</li>
		</ul>

		<Dialog v-model:visible="dialog" modal :header="current?.name ?? ctrans('Upload invoice')" :style="{ width: '46rem', maxWidth: 'calc(100vw - 2rem)' }">
			<div v-if="!current" class="space-y-4 text-sm text-gray-600">
				<p>{{ ctrans("Upload the invoice of the forwarder, customs broker or haulier. It is read here for you to check, and nothing is costed until you add it.") }}</p>
				<div class="flex flex-wrap items-center gap-3">
					<FileUpload
						mode="basic"
						auto
						customUpload
						accept=".pdf,.xlsx,.xls,.csv,image/*"
						:maxFileSize="50000000"
						:disabled="busy === 'upload'"
						:chooseLabel="ctrans('Choose the invoice')"
						:pt="{ pcChooseButton: { root: { class: '!border !border-gray-300 !bg-white !px-3 !py-1.5 !text-sm !text-gray-700 hover:!bg-gray-50' } } }"
						@uploader="upload"
					>
						<template #chooseicon>
							<FontAwesomeIcon icon="fal fa-paperclip" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</FileUpload>
					<span v-if="busy === 'upload'" class="flex items-center gap-1 text-gray-500">
						<FontAwesomeIcon :icon="faSpinnerThird" spin fixed-width aria-hidden="true" />{{ ctrans("Uploading…") }}
					</span>
					<Button type="tertiary" class="ml-auto" :label="ctrans('Type it by hand')" @click="typeByHand">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-pencil" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>

			<div v-else-if="current.reading?.state === 'reading'" class="flex items-center gap-2 py-6 text-sm text-gray-600">
				<FontAwesomeIcon :icon="faSpinnerThird" spin fixed-width aria-hidden="true" />
				{{ ctrans("Reading the invoice. Two readers check it against each other, this takes up to a minute.") }}
			</div>

			<div v-else-if="current.reading?.state !== 'read'" class="space-y-3 text-sm">
				<p class="text-red-600">{{ current.reading?.error ?? ctrans("The invoice could not be read.") }}</p>
				<div class="flex justify-end">
					<Button v-if="canEdit" type="tertiary" :label="ctrans('Read again')" :loading="busy === current.media_id" @click="read(current)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-redo" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>

			<div v-else-if="types[draftKey(current)]" class="space-y-3 text-sm">
				<ul v-if="current.reading.problems?.length" class="space-y-1 text-amber-700">
					<li v-for="problem in current.reading.problems" :key="problem" class="flex gap-1.5">
						<FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5" fixed-width aria-hidden="true" />
						<span>{{ problem }}</span>
					</li>
					<li class="text-gray-600">{{ ctrans("A reading that fails a check is never corrected here: add this invoice by hand with Add.") }}</li>
				</ul>

				<a :href="route('grp.media.download', { id: current.ulid })" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:underline">
					<FontAwesomeIcon icon="fal fa-paperclip" fixed-width aria-hidden="true" />{{ ctrans("Open the document") }}
				</a>

				<dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs sm:grid-cols-4">
					<div><dt class="text-gray-500">{{ ctrans("Issued by") }}</dt><dd class="text-gray-800">{{ current.reading.issuer ?? "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Invoice reference") }}</dt><dd class="text-gray-800">{{ current.reading.invoice_number ?? "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Invoice date") }}</dt><dd class="text-gray-800">{{ current.reading.invoice_date ? useFormatTime(current.reading.invoice_date) : "—" }}</dd></div>
					<div><dt class="text-gray-500">{{ ctrans("Currency") }}</dt><dd class="text-gray-800">{{ current.reading.currency ?? "—" }}</dd></div>
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
						<tr v-for="(line, index) in current.reading.lines" :key="index" class="border-t border-gray-100">
							<td class="py-1 pr-2 text-gray-800">{{ line.description ?? "—" }}</td>
							<td class="py-1 pr-2">
								<Select v-model="types[draftKey(current)][index]" :options="props.types" optionLabel="label" optionValue="value" size="small"
									:placeholder="ctrans('The readers do not agree, choose')" :disabled="!canEdit || !current.reading.can_apply"
									:invalid="!types[draftKey(current)][index]" class="w-full" :class="fieldFocusClass" />
							</td>
							<td class="py-1 text-right tabular-nums text-gray-800">{{ money(line.amount, current.reading.currency) }}</td>
						</tr>
					</tbody>
					<tfoot class="border-t border-gray-300 text-gray-800">
						<tr>
							<td colspan="2" class="py-1 text-right text-gray-500">{{ ctrans("Total before VAT") }}</td>
							<td class="py-1 text-right font-medium tabular-nums">{{ money(current.reading.net_total, current.reading.currency) }}</td>
						</tr>
						<tr v-if="current.reading.vat_total">
							<td colspan="2" class="py-1 text-right text-gray-500">{{ ctrans("VAT, not part of the cost") }}</td>
							<td class="py-1 text-right tabular-nums">{{ money(current.reading.vat_total, current.reading.currency) }}</td>
						</tr>
					</tfoot>
				</table>

				<p class="text-xs text-gray-500">
					{{ ctrans("The amounts are the ones printed on the document and cannot be changed here. Lines of the same type are added as one service invoice.") }}
				</p>

				<StockDeliveryHandTypedCostWarning v-if="current.reading.can_apply" :handTyped="handTyped" :billed="billed(current)" :orgCurrency="orgCurrency" />

				<label v-if="current.reading.needs_manual_check && current.reading.can_apply" class="flex items-start gap-2 text-xs text-amber-700">
					<Checkbox v-model="checked[draftKey(current)]" binary :inputId="`service-invoice-reading-checked-${current.media_id}`" />
					<span>{{ ctrans("This document could not be checked automatically. I have checked every amount, and that it is of this delivery, against the document.") }}</span>
				</label>

				<div v-if="current.reading.delivery_confirmation && current.reading.can_apply" class="space-y-2 rounded border p-3"
					:class="current.reading.delivery_confirmation.dates_fit ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-red-200 bg-red-50 text-red-700'">
					<p>
						<FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
						{{ ctrans("The document does not mention :reference.", { reference: current.reading.delivery_confirmation.reference }) }}
						<template v-if="current.reading.delivery_confirmation.printed.length">
							{{ ctrans("It prints these references:") }}
							<span class="font-medium">{{ current.reading.delivery_confirmation.printed.join(", ") }}</span>
						</template>
					</p>
					<p v-if="current.reading.delivery_confirmation.dates_fit">
						{{ ctrans("Its date, :date, fits this delivery, which left on :left.", { date: String(useFormatTime(current.reading.invoice_date ?? "")), left: String(useFormatTime(current.reading.delivery_confirmation.left_at ?? "")) }) }}
					</p>
					<p v-else-if="current.reading.delivery_confirmation.dates_fit === false" class="font-medium">
						{{ ctrans("Its date, :date, does not fit this delivery either, which left on :left. It may be the invoice of another delivery.", { date: String(useFormatTime(current.reading.invoice_date ?? "")), left: String(useFormatTime(current.reading.delivery_confirmation.left_at ?? "")) }) }}
						<template v-if="current.reading.delivery_confirmation.arrived_at">
							{{ ctrans("It arrived on :arrived.", { arrived: String(useFormatTime(current.reading.delivery_confirmation.arrived_at)) }) }}
						</template>
					</p>
					<label v-if="canEdit" class="block space-y-1">
						<span>{{ ctrans("To add it, type: :phrase", { phrase: current.reading.delivery_confirmation.phrase }) }}</span>
						<InputText v-model="confirmation" size="small" class="w-full" :class="fieldFocusClass" autocomplete="off" :placeholder="current.reading.delivery_confirmation.phrase" />
					</label>
				</div>

				<div class="flex justify-end gap-2 pt-1">
					<Button v-if="canEdit" type="tertiary" class="mr-auto" :label="ctrans('Read again')" :loading="busy === current.media_id" @click="read(current)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-redo" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Cancel')" @click="dialog = false">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button v-if="canEdit && current.reading.can_apply" type="tertiary" :label="ctrans('Add to the service invoices')" :disabled="!canApply(current)" :loading="busy === current.media_id" @click="apply(current)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>
	</div>
</template>

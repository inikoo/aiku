<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 10:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router, useForm } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ZoomableImage from "@/Components/Utils/ZoomableImage.vue"
import ConfirmDialog from "primevue/confirmdialog"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import FileUpload from "primevue/fileupload"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import { useConfirm } from "primevue/useconfirm"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faInboxIn, faClipboardCheck, faPencil, faTrashAlt, faChevronDown, faChevronRight, faCheck, faTimes, faExclamationTriangle, faImage, faBoxCheck, faFilePdf } from "@fal"

type Receipt = {
	id: number
	received_at: string
	quantity_received: number
	notes: string | null
	quantity_rejected: number | null
	is_checked: boolean
	checked_at: string | null
	reject_reason: string | null
	received_by: string | null
	checked_by: string | null
	is_packed: boolean
	photos: { id: number; name: string; url: string }[]
	update_route: routeType | null
	delete_route: routeType | null
}

type Line = {
	id: number
	code: string | null
	name: string | null
	image_thumbnail: object | null
	image_full: { original_url: string; download_url: string } | null
	state: string
	units_per_pack: number
	ordered: number
	received: number
	rejected: number
	accepted: number
	outstanding: number
	unchecked: number
	store_route: routeType | null
	receipts: Receipt[]
}

const props = defineProps<{
	receiving: {
		can_edit: boolean
		lines: Line[]
		receipt_note: { route: routeType; dates: string[] }
	}
}>()

const locale = useLocaleStore()
const confirm = useConfirm()

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const formatQuantity = (value: number | null) => locale.number(Math.round(Number(value ?? 0) * 1000) / 1000)
const skos = (line: Line, units: number | null) => formatQuantity(Number(units ?? 0) / (line.units_per_pack || 1))
const toReplace = (line: Line) => Math.min(line.outstanding, line.rejected)

function isoDate(date: Date | null) {
	if (!date) {
		return null
	}

	return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
}

const totals = computed(() => props.receiving.lines.reduce(
	(sum, line) => ({
		received: sum.received + line.received,
		rejected: sum.rejected + line.rejected,
		outstanding: sum.outstanding + line.outstanding,
		unchecked: sum.unchecked + line.unchecked,
	}),
	{ received: 0, rejected: 0, outstanding: 0, unchecked: 0 },
))

const expanded = ref<Record<number, boolean>>({})
const isExpanded = (line: Line) => expanded.value[line.id] ?? line.unchecked > 0

const receivingLine = ref<Line | null>(null)
const editingReceipt = ref<Receipt | null>(null)

const receiveForm = useForm<{ received_at: Date | null; quantity_received: number | null; notes: string | null }>({
	received_at: new Date(),
	quantity_received: null,
	notes: null,
})

function openReceive(line: Line) {
	receivingLine.value = line
	editingReceipt.value = null
	receiveForm.clearErrors()
	receiveForm.received_at = new Date()
	receiveForm.quantity_received = line.outstanding
	receiveForm.notes = null
}

function openEditReceipt(line: Line, receipt: Receipt) {
	receivingLine.value = line
	editingReceipt.value = receipt
	receiveForm.clearErrors()
	receiveForm.received_at = new Date(receipt.received_at)
	receiveForm.quantity_received = receipt.quantity_received
	receiveForm.notes = receipt.notes
}

function closeReceive() {
	receivingLine.value = null
	editingReceipt.value = null
}

const receiveMaximum = computed(() => {
	const line = receivingLine.value
	if (!line) {
		return 0
	}

	return Math.max(line.ordered - line.accepted + (editingReceipt.value?.quantity_received ?? 0), 0)
})

const editingRejected = computed(() => Number(editingReceipt.value?.quantity_rejected ?? 0))
const isBelowRejected = computed(() => editingRejected.value > 0 && Number(receiveForm.quantity_received ?? 0) < editingRejected.value)

function submitReceive() {
	const targetRoute = editingReceipt.value ? editingReceipt.value.update_route : receivingLine.value?.store_route
	if (!targetRoute) {
		return
	}

	receiveForm
		.transform((data) => ({ ...data, received_at: isoDate(data.received_at), notes: data.notes || null }))
		.post(route(targetRoute.name, targetRoute.parameters), {
			preserveScroll: true,
			onSuccess: closeReceive,
		})
}

const checkingLine = ref<Line | null>(null)
const checkingReceipt = ref<Receipt | null>(null)

const checkForm = useForm<{ quantity_rejected: number | null; reject_reason: string | null; photos: File[] }>({
	quantity_rejected: 0,
	reject_reason: null,
	photos: [],
})

function openCheck(line: Line, receipt: Receipt) {
	checkingLine.value = line
	checkingReceipt.value = receipt
	checkForm.clearErrors()
	checkForm.quantity_rejected = receipt.quantity_rejected ?? 0
	checkForm.reject_reason = receipt.reject_reason
	checkForm.photos = []
}

function closeCheck() {
	checkingLine.value = null
	checkingReceipt.value = null
}

function submitCheck() {
	const targetRoute = checkingReceipt.value?.update_route
	if (!targetRoute) {
		return
	}

	checkForm
		.transform((data) => ({
			quantity_rejected: data.quantity_rejected ?? 0,
			reject_reason: data.reject_reason || null,
			...(data.photos.length ? { photos: data.photos } : {}),
		}))
		.post(route(targetRoute.name, targetRoute.parameters), {
			forceFormData: true,
			preserveScroll: true,
			onSuccess: closeCheck,
		})
}

function confirmDeleteReceipt(line: Line, receipt: Receipt) {
	const deleteRoute = receipt.delete_route
	if (!deleteRoute) {
		return
	}

	confirm.require({
		group: "agent-receiving",
		header: ctrans("Remove this receipt?"),
		message: ctrans(":units units of :code received on :date leave our warehouse and are owed by the supplier again.", {
			units: formatQuantity(receipt.quantity_received - Number(receipt.quantity_rejected ?? 0)),
			code: line.code ?? "",
			date: receipt.received_at,
		}),
		acceptProps: { label: ctrans("Remove"), severity: "danger" },
		rejectProps: { label: ctrans("Keep") },
		accept: () => router.delete(route(deleteRoute.name, deleteRoute.parameters), { preserveScroll: true }),
	})
}

const localDate = (date: string) => new Date(`${date}T00:00:00`)

const isReceiptNoteOpen = ref(false)
const receiptNoteDates = ref<(Date | null)[] | null>(null)

function openReceiptNote() {
	const latest = props.receiving.receipt_note.dates[0]
	receiptNoteDates.value = latest ? [localDate(latest), localDate(latest)] : null
	isReceiptNoteOpen.value = true
}

function isOnlyReceiptNoteDate(date: string) {
	const [from, to] = receiptNoteDates.value ?? []

	return isoDate(from ?? null) === date && isoDate(to ?? from ?? null) === date
}

function downloadReceiptNote() {
	const [from, to] = receiptNoteDates.value ?? []
	if (!from) {
		return
	}

	const noteRoute = props.receiving.receipt_note.route
	window.open(route(noteRoute.name, { ...noteRoute.parameters, from: isoDate(from), to: isoDate(to ?? from) }), "_blank")
	isReceiptNoteOpen.value = false
}

function receiptStatus(receipt: Receipt) {
	if (!receipt.is_checked) {
		return { label: ctrans("Not checked yet"), class: "bg-amber-50 text-amber-700 ring-amber-200" }
	}

	if (Number(receipt.quantity_rejected) > 0) {
		return { label: ctrans(":units rejected", { units: formatQuantity(receipt.quantity_rejected) }), class: "bg-red-50 text-red-700 ring-red-200" }
	}

	return { label: ctrans("No rejects"), class: "bg-green-50 text-green-700 ring-green-200" }
}
</script>

<template>
	<section class="border-b border-gray-300 px-4 py-3">
		<div class="flex flex-wrap items-center justify-between gap-2">
			<h3 class="flex items-center gap-2 font-semibold text-gray-800">
				<FontAwesomeIcon :icon="faInboxIn" class="text-[--app-accent]" fixed-width aria-hidden="true" />
				{{ ctrans("Received at our warehouse") }}
			</h3>
			<div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
				<span>{{ ctrans("Received") }}: <span class="font-medium tabular-nums text-gray-800">{{ formatQuantity(totals.received) }}</span></span>
				<span>{{ ctrans("Rejected") }}: <span class="font-medium tabular-nums" :class="totals.rejected > 0 ? 'text-red-600' : 'text-gray-800'">{{ formatQuantity(totals.rejected) }}</span></span>
				<span>{{ ctrans("Still to come") }}: <span class="font-medium tabular-nums text-gray-800">{{ formatQuantity(totals.outstanding) }}</span></span>
				<span v-if="totals.unchecked" class="rounded-full bg-amber-50 px-2 text-amber-700 ring-1 ring-inset ring-amber-200">
					{{ ctrans(":count not checked yet", { count: totals.unchecked }) }}
				</span>
				<Button
					type="tertiary"
					size="xs"
					:label="ctrans('Receipt note')"
					:tooltip="ctrans('PDF for the supplier with the products received on the dates you choose')"
					:disabled="!receiving.receipt_note.dates.length"
					@click="openReceiptNote"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faFilePdf" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</div>
		<p class="mt-1 text-xs text-gray-500">
			{{ ctrans("Record each batch as it arrives from the supplier, then check it for rejects. Rejected units go back to In Production: the supplier still owes them. Quantities are in units.") }}
		</p>

		<div class="mt-3 overflow-x-auto">
			<table class="min-w-full text-sm">
				<thead>
					<tr class="border-b border-gray-200 text-left text-xs text-gray-500">
						<th class="w-6"></th>
						<th class="py-1.5 pr-3 font-normal">{{ ctrans("Product") }}</th>
						<th class="px-3 py-1.5 text-right font-normal">{{ ctrans("Ordered") }}</th>
						<th class="px-3 py-1.5 text-right font-normal">{{ ctrans("Received") }}</th>
						<th class="px-3 py-1.5 text-right font-normal">{{ ctrans("Rejected") }}</th>
						<th class="px-3 py-1.5 text-right font-normal">{{ ctrans("Still to come") }}</th>
						<th class="py-1.5 pl-3"></th>
					</tr>
				</thead>
				<tbody>
					<template v-for="line in receiving.lines" :key="line.id">
						<tr class="border-b border-gray-100" :class="{ 'opacity-50': line.state === 'cancelled' }">
							<td class="align-middle">
								<button
									v-if="line.receipts.length"
									type="button"
									class="text-gray-400 hover:text-gray-700"
									:aria-label="ctrans('Show the batches received')"
									@click="expanded[line.id] = !isExpanded(line)"
								>
									<FontAwesomeIcon :icon="isExpanded(line) ? faChevronDown : faChevronRight" fixed-width aria-hidden="true" />
								</button>
							</td>
							<td class="py-2 pr-3">
								<div class="flex items-center gap-2">
									<ZoomableImage :src="line.image_thumbnail" :full="line.image_full" :alt="line.code" class="h-10 w-10" />
									<div>
										<div class="font-medium text-gray-800">{{ line.code }}</div>
										<div class="text-xs text-gray-500">{{ line.name }}</div>
									</div>
									<span
										v-if="line.unchecked"
										class="whitespace-nowrap rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700 ring-1 ring-inset ring-amber-200"
									>
										{{ ctrans("Not checked yet") }}
									</span>
								</div>
							</td>
							<td class="px-3 py-2 text-right tabular-nums" v-tooltip="ctrans(':skos SKO', { skos: skos(line, line.ordered) })">{{ formatQuantity(line.ordered) }}</td>
							<td class="px-3 py-2 text-right tabular-nums" v-tooltip="ctrans(':skos SKO', { skos: skos(line, line.received) })">{{ formatQuantity(line.received) }}</td>
							<td class="px-3 py-2 text-right tabular-nums" :class="line.rejected > 0 ? 'text-red-600' : ''">{{ formatQuantity(line.rejected) }}</td>
							<td class="px-3 py-2 text-right font-medium tabular-nums" v-tooltip="ctrans(':skos SKO', { skos: skos(line, line.outstanding) })">
								<FontAwesomeIcon v-if="line.outstanding === 0 && line.received > 0" :icon="faBoxCheck" class="mr-1 text-green-600" fixed-width aria-hidden="true" />
								{{ formatQuantity(line.outstanding) }}
								<div v-if="toReplace(line) > 0" class="text-xs font-normal text-red-600">
									{{ ctrans(":units to replace rejects", { units: formatQuantity(toReplace(line)) }) }}
								</div>
							</td>
							<td class="py-2 pl-3 text-right">
								<Button v-if="line.store_route" type="tertiary" size="xs" :label="ctrans('Receive')" @click="openReceive(line)">
									<template #icon>
										<FontAwesomeIcon :icon="faInboxIn" class="text-[--app-accent]" fixed-width aria-hidden="true" />
									</template>
								</Button>
							</td>
						</tr>

						<template v-if="isExpanded(line)">
						<tr v-for="receipt in line.receipts" :key="`receipt-${receipt.id}`" class="border-b border-gray-100 bg-gray-50/60 text-xs">
							<td></td>
							<td class="py-1.5 pr-3" colspan="2">
								<div class="text-gray-700">
									{{ ctrans(":units units on :date", { units: formatQuantity(receipt.quantity_received), date: receipt.received_at }) }}
									<span class="text-gray-400">({{ skos(line, receipt.quantity_received) }} SKO)</span>
								</div>
								<div v-if="receipt.notes" class="mt-0.5 whitespace-pre-line text-gray-600">{{ receipt.notes }}</div>
								<div v-if="receipt.received_by" class="text-gray-400">{{ ctrans("Received by :name", { name: receipt.received_by }) }}</div>
							</td>
							<td class="px-3 py-1.5" colspan="3">
								<span class="whitespace-nowrap rounded-full px-2 py-0.5 ring-1 ring-inset" :class="receiptStatus(receipt).class">
									{{ receiptStatus(receipt).label }}
								</span>
								<span v-if="receipt.is_packed" class="ml-2 text-gray-500">{{ ctrans("Packed for a container") }}</span>
								<div v-if="receipt.reject_reason" class="mt-1 whitespace-pre-line text-gray-600">{{ receipt.reject_reason }}</div>
								<div v-if="receipt.photos.length" class="mt-1 flex flex-wrap gap-2">
									<a v-for="photo in receipt.photos" :key="photo.id" :href="photo.url" target="_blank" class="secondaryLink inline-flex items-center gap-1">
										<FontAwesomeIcon :icon="faImage" fixed-width aria-hidden="true" />
										{{ photo.name }}
									</a>
								</div>
								<div v-if="receipt.checked_by" class="mt-0.5 text-gray-400">{{ ctrans("Checked by :name", { name: receipt.checked_by }) }}</div>
							</td>
							<td class="py-1.5 pl-3">
								<div v-if="receipt.update_route" class="flex justify-end gap-1.5">
									<Button type="tertiary" size="xs" :label="receipt.is_checked ? ctrans('Check again') : ctrans('Check')" @click="openCheck(line, receipt)">
										<template #icon>
											<FontAwesomeIcon :icon="faClipboardCheck" class="text-green-600" fixed-width aria-hidden="true" />
										</template>
									</Button>
									<Button type="tertiary" size="xs" :tooltip="ctrans('Correct the date or the units received')" @click="openEditReceipt(line, receipt)">
										<template #icon>
											<FontAwesomeIcon :icon="faPencil" class="text-gray-500" fixed-width aria-hidden="true" />
										</template>
									</Button>
									<Button type="tertiary" size="xs" :tooltip="ctrans('Remove this receipt')" @click="confirmDeleteReceipt(line, receipt)">
										<template #icon>
											<FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
										</template>
									</Button>
								</div>
							</td>
						</tr>
						</template>
					</template>
				</tbody>
			</table>
		</div>

		<Dialog
			:visible="!!receivingLine"
			modal
			:header="editingReceipt ? ctrans('Correct receipt of :code', { code: receivingLine?.code ?? '' }) : ctrans('Receive :code', { code: receivingLine?.code ?? '' })"
			:style="{ width: '28rem' }"
			:breakpoints="{ '640px': '95vw' }"
			@update:visible="(visible) => !visible && closeReceive()"
		>
			<div v-if="receivingLine" class="space-y-3 text-sm">
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Date received") }}</span>
					<DatePicker v-model="receiveForm.received_at" dateFormat="yy-mm-dd" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" fluid />
				</label>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Units received") }}</span>
					<InputNumber v-model="receiveForm.quantity_received" :min="0" :max="receiveMaximum" :maxFractionDigits="3" :class="fieldFocusClass" fluid />
					<span class="block text-xs text-gray-500">
						{{ ctrans("= :skos SKO. Up to :units units are still to come.", { skos: skos(receivingLine, receiveForm.quantity_received), units: formatQuantity(receiveMaximum) }) }}
					</span>
				</label>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Remarks (optional)") }}</span>
					<Textarea v-model="receiveForm.notes" rows="2" autoResize fluid :placeholder="ctrans('Printed on the receipt note for the supplier')" />
				</label>
				<div
					v-if="editingRejected > 0"
					class="rounded-md px-3 py-2 text-xs ring-1 ring-inset"
					:class="isBelowRejected ? 'bg-red-50 text-red-700 ring-red-200' : 'bg-amber-50 text-amber-700 ring-amber-200'"
				>
					{{ ctrans(":rejected units of this batch were rejected: units received cannot be less than :rejected.", { rejected: formatQuantity(editingRejected) }) }}
				</div>
				<div v-for="(error, field) in receiveForm.errors" :key="field" class="text-xs text-red-600">{{ error }}</div>
				<div class="flex justify-end gap-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click="closeReceive">
						<template #icon>
							<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Save')" :loading="receiveForm.processing" :disabled="!receiveForm.quantity_received || !receiveForm.received_at || isBelowRejected" @click="submitReceive">
						<template #icon>
							<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>

		<Dialog
			:visible="!!checkingReceipt"
			modal
			:header="ctrans('Check :code received on :date', { code: checkingLine?.code ?? '', date: checkingReceipt?.received_at ?? '' })"
			:style="{ width: '30rem' }"
			:breakpoints="{ '640px': '95vw' }"
			@update:visible="(visible) => !visible && closeCheck()"
		>
			<div v-if="checkingReceipt && checkingLine" class="space-y-3 text-sm">
				<p class="text-gray-600">
					{{ ctrans(":units units were received. Enter how many are rejected; enter 0 when all are good.", { units: formatQuantity(checkingReceipt.quantity_received) }) }}
				</p>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Units rejected") }}</span>
					<InputNumber v-model="checkForm.quantity_rejected" :min="0" :max="checkingReceipt.quantity_received" :maxFractionDigits="3" :class="fieldFocusClass" fluid />
					<span class="block text-xs text-gray-500">
						{{ ctrans("= :skos SKO. Rejected units go back to In Production.", { skos: skos(checkingLine, checkForm.quantity_rejected) }) }}
					</span>
				</label>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Reason (optional)") }}</span>
					<Textarea v-model="checkForm.reject_reason" rows="2" autoResize fluid />
				</label>
				<div class="space-y-1">
					<span class="block text-xs text-gray-500">{{ ctrans("Photos (optional)") }}</span>
					<div v-if="checkingReceipt.photos.length" class="flex flex-wrap gap-2 text-xs">
						<a v-for="photo in checkingReceipt.photos" :key="photo.id" :href="photo.url" target="_blank" class="secondaryLink inline-flex items-center gap-1">
							<FontAwesomeIcon :icon="faImage" fixed-width aria-hidden="true" />
							{{ photo.name }}
						</a>
					</div>
					<FileUpload mode="basic" multiple accept="image/*" :auto="false" :chooseLabel="ctrans('Add photos')" @select="(event) => (checkForm.photos = event.files)" />
				</div>
				<div v-for="(error, field) in checkForm.errors" :key="field" class="text-xs text-red-600">{{ error }}</div>
				<div class="flex justify-end gap-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click="closeCheck">
						<template #icon>
							<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Save check')" :loading="checkForm.processing" @click="submitCheck">
						<template #icon>
							<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>

		<Dialog
			v-model:visible="isReceiptNoteOpen"
			modal
			:header="ctrans('Receipt note for the supplier')"
			:style="{ width: '26rem' }"
			:breakpoints="{ '640px': '95vw' }"
		>
			<div class="space-y-3 text-sm">
				<p class="text-gray-600">{{ ctrans("Pick a day, or a first and a last day, to list the products received on those dates.") }}</p>
				<DatePicker
					v-model="receiptNoteDates"
					selectionMode="range"
					dateFormat="yy-mm-dd"
					:maxDate="new Date()"
					:manualInput="false"
					showIcon
					iconDisplay="input"
					:class="fieldFocusClass"
					fluid
				/>
				<div class="space-y-1">
					<span class="block text-xs text-gray-500">{{ ctrans("Days with goods received") }}</span>
					<div class="flex flex-wrap gap-1.5">
						<button
							v-for="date in receiving.receipt_note.dates"
							:key="date"
							type="button"
							class="rounded-full border px-2 py-0.5 text-xs"
							:class="isOnlyReceiptNoteDate(date) ? 'border-[--app-accent] bg-[--app-accent-soft] text-[--app-accent-strong]' : 'border-gray-200 text-gray-600 hover:border-[--app-accent-muted]'"
							@click="receiptNoteDates = [localDate(date), localDate(date)]"
						>
							{{ date }}
						</button>
					</div>
				</div>
				<div class="flex justify-end gap-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click="isReceiptNoteOpen = false">
						<template #icon>
							<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Open PDF')" :disabled="!receiptNoteDates?.[0]" @click="downloadReceiptNote">
						<template #icon>
							<FontAwesomeIcon :icon="faFilePdf" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>

		<ConfirmDialog group="agent-receiving" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }">
			<template #container="{ message, acceptCallback, rejectCallback }">
				<div class="rounded-lg bg-white p-5">
					<div class="flex items-center gap-2 font-semibold text-gray-900">
						<FontAwesomeIcon :icon="faExclamationTriangle" class="text-orange-500" fixed-width aria-hidden="true" />
						{{ message.header }}
					</div>
					<p class="mt-3 text-sm text-gray-600">{{ message.message }}</p>
					<div class="mt-5 flex justify-end gap-2">
						<Button type="tertiary" :label="message.rejectProps?.label ?? ctrans('Cancel')" @click="rejectCallback">
							<template #icon>
								<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
							</template>
						</Button>
						<Button type="tertiary" :label="message.acceptProps?.label ?? ctrans('Confirm')" @click="acceptCallback">
							<template #icon>
								<FontAwesomeIcon :icon="faExclamationTriangle" class="text-amber-500" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</div>
				</div>
			</template>
		</ConfirmDialog>
	</section>
</template>

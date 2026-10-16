<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, nextTick, ref, watch } from "vue"
import axios from "axios"
import { router } from "@inertiajs/vue3"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import FileUpload from "primevue/fileupload"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import Textarea from "primevue/textarea"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faInfoCircle, faExclamationTriangle, faIndustryAlt, faFileInvoice, faMagic, faEraser } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

type ScopeOption = { value: number; label: string; count: number }

type ReceivableLine = {
	id: number
	code: string
	name: string | null
	supplier_code: string | null
	organisation_code: string
	purchase_order_reference: string
	quantity_ordered: number
	quantity: number
	units_per_sko: number
}

type ScopeType = "supplier" | "purchase_order"

const props = defineProps<{
	scopes: { suppliers: ScopeOption[]; purchase_orders: ScopeOption[] }
	purchaseOrderId?: number | null
}>()

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()
const organisation = route().params["organisation"]

const scopeType = ref<ScopeType>("supplier")
const scopeId = ref<number | null>(null)
const lines = ref<ReceivableLine[]>([])
const isLoading = ref(false)
const quantities = ref<Record<number, number | null>>({})
const pasted = ref("")
const unmatchedCodes = ref<string[]>([])
const search = ref("")
const notes = ref("")
const receivedAt = ref<Date | null>(new Date())
const deliveryNote = ref("")
const photos = ref<File[]>([])
const photoUploader = ref<InstanceType<typeof FileUpload> | null>(null)
const serverErrors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

const scopeTypeOptions = computed(() => [
	{ label: ctrans("Supplier"), value: "supplier" as ScopeType, icon: faIndustryAlt },
	{ label: ctrans("Purchase order"), value: "purchase_order" as ScopeType, icon: faFileInvoice },
])

const scopeOptions = computed(() => scopeType.value === "supplier" ? props.scopes.suppliers : props.scopes.purchase_orders)

const resetLines = () => {
	lines.value = []
	quantities.value = {}
	unmatchedCodes.value = []
	serverErrors.value = {}
}

watch(visible, async (isVisible) => {
	if (isVisible) {
		scopeId.value = null
		if (props.purchaseOrderId) {
			scopeType.value = "purchase_order"
			await nextTick()
			scopeId.value = props.purchaseOrderId
		}
		pasted.value = ""
		search.value = ""
		notes.value = ""
		receivedAt.value = new Date()
		deliveryNote.value = ""
		photos.value = []
		resetLines()
	}
})

watch(scopeType, () => {
	scopeId.value = null
})

watch(scopeId, async (id) => {
	resetLines()
	if (!id) {
		return
	}

	isLoading.value = true
	try {
		const { data } = await axios.get(route("grp.org.agent.warehouse.in_production.to_receive", { organisation, [scopeType.value]: id }))
		if (scopeId.value === id) {
			lines.value = data
		}
	} catch {
		notify({ title: ctrans("Something went wrong"), text: ctrans("The lines in production could not be loaded"), type: "error" })
	} finally {
		isLoading.value = false
	}
})

const round = (value: number) => Math.round(value * 1000) / 1000

const formatNumber = (value: number) => locale.number(round(value))

const unitsPerSko = (line: ReceivableLine) => Number(line.units_per_sko) || 1

const parsePasted = (text: string): Array<{ code: string; quantity: number }> => text
	.split(/\r?\n/)
	.map(row => row.trim())
	.filter(Boolean)
	.map(row => {
		const match = row.match(/^(.+?)[\s,;]+([\d.,]+)$/)

		return match ? { code: match[1].trim(), quantity: Number(match[2].replace(/,/g, "")) } : { code: row, quantity: NaN }
	})

const applyPasted = () => {
	const next: Record<number, number | null> = { ...quantities.value }
	const unmatched: string[] = []

	for (const { code, quantity } of parsePasted(pasted.value)) {
		const candidates = lines.value.filter(line => line.code.toLowerCase() === code.toLowerCase())
		if (candidates.length === 0 || !(quantity > 0)) {
			unmatched.push(Number.isNaN(quantity) ? code : `${code} ${quantity}`)
			continue
		}

		let left = quantity
		for (const line of candidates) {
			const room = round(line.quantity - (next[line.id] ?? 0))
			if (left <= 0 || room <= 0) {
				continue
			}
			const take = Math.min(room, left)
			next[line.id] = round((next[line.id] ?? 0) + take)
			left = round(left - take)
		}

		if (left > 0) {
			const last = candidates[candidates.length - 1]
			next[last.id] = round((next[last.id] ?? 0) + left)
		}
	}

	quantities.value = next
	unmatchedCodes.value = unmatched
	serverErrors.value = {}
}

const clearQuantities = () => {
	quantities.value = {}
	unmatchedCodes.value = []
	serverErrors.value = {}
}

const filteredLines = computed(() => {
	const term = search.value.trim().toLowerCase()

	return term
		? lines.value.filter(line => [line.code, line.name, line.purchase_order_reference, line.supplier_code].some(value => value?.toLowerCase().includes(term)))
		: lines.value
})

const problem = (line: ReceivableLine): string | null => {
	const quantity = quantities.value[line.id]
	if (serverErrors.value[`lines.${line.id}`]) {
		return serverErrors.value[`lines.${line.id}`]
	}
	if (quantity && round(quantity) > round(line.quantity)) {
		return ctrans("Only :units units are still in production", { units: formatNumber(line.quantity) })
	}

	return null
}

const selectedLines = computed(() => lines.value.filter(line => (quantities.value[line.id] ?? 0) > 0))

const hasProblem = computed(() => selectedLines.value.some(line => problem(line) !== null))

const totalUnits = computed(() => selectedLines.value.reduce((sum, line) => sum + (quantities.value[line.id] ?? 0), 0))

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent] focus:!border-[--app-accent]"

const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const toIsoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const removePhoto = (index: number) => {
	photos.value = photos.value.filter((_, position) => position !== index)
}

const submit = () => {
	if (selectedLines.value.length === 0 || hasProblem.value) {
		return
	}

	router.post(route("grp.org.agent.warehouse.in_production.receive", { organisation }), {
		lines: selectedLines.value.map(line => ({
			purchase_order_transaction_id: line.id,
			quantity: round(quantities.value[line.id] ?? 0),
		})),
		received_at: receivedAt.value ? toIsoDate(receivedAt.value) : null,
		supplier_delivery_note: deliveryNote.value.trim() || null,
		notes: notes.value.trim() || null,
		attachments: photos.value,
	}, {
		forceFormData: true,
		preserveScroll: true,
		onStart: () => { isSubmitting.value = true },
		onSuccess: () => { visible.value = false },
		onFinish: () => { isSubmitting.value = false },
		onError: (errors: Record<string, string>) => {
			serverErrors.value = errors
			notify({ title: ctrans("The goods receipt was not saved"), text: Object.values(errors)[0] ?? ctrans("Something went wrong"), type: "error" })
		},
	})
}
</script>

<template>
	<Dialog
		v-model:visible="visible"
		modal
		:header="ctrans('Receive goods from the suppliers')"
		:style="{ width: '60rem', maxWidth: 'calc(100vw - 2rem)' }"
		:draggable="false"
	>
		<div class="mb-3 flex gap-2 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] p-3 text-sm text-gray-700">
			<FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-[--app-accent]" fixed-width aria-hidden="true" />
			<p>{{ ctrans("Choose the supplier or the purchase order the goods came from, then paste or type one code and quantity in units per line. Each time you receive makes one goods receipt, with the signed delivery note photo. What you receive moves to Received; what is still missing stays in production.") }}</p>
		</div>

		<div class="mb-3 flex flex-wrap items-center gap-3">
			<SegmentedToggle v-model="scopeType" :options="scopeTypeOptions" :ariaLabel="ctrans('Goods came from')" />
			<Select
				v-model="scopeId"
				:options="scopeOptions"
				optionLabel="label"
				optionValue="value"
				filter
				showClear
				class="min-w-[18rem] flex-1"
				:placeholder="scopeType === 'supplier' ? ctrans('Choose a supplier') : ctrans('Choose a purchase order')"
				:emptyMessage="ctrans('Nothing in production')"
			>
				<template #option="{ option }">
					<div class="flex w-full items-center gap-3">
						<span class="whitespace-nowrap">{{ option.label }}</span>
						<span class="ml-auto text-xs tabular-nums text-gray-500">{{ ctrans(":count lines", { count: option.count }) }}</span>
					</div>
				</template>
			</Select>
		</div>

		<div v-if="scopeId" class="grid gap-3 md:grid-cols-[18rem_1fr]">
			<div class="flex flex-col gap-2">
				<label for="agent-receive-paste" class="text-sm font-medium text-gray-700">{{ ctrans("Code and quantity") }}</label>
				<Textarea
					id="agent-receive-paste"
					v-model="pasted"
					rows="10"
					class="font-mono text-sm"
					:placeholder="'ACShop-12   24\nRaffP-02    120'"
				/>
				<div class="flex gap-2">
					<Button type="tertiary" :label="ctrans('Fill quantities')" :disabled="!pasted.trim() || isLoading" @click="applyPasted">
						<template #icon>
							<FontAwesomeIcon :icon="faMagic" class="text-[--app-accent]" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Clear')" :disabled="selectedLines.length === 0" @click="clearQuantities">
						<template #icon>
							<FontAwesomeIcon :icon="faEraser" class="text-gray-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
				<div v-if="unmatchedCodes.length" class="rounded-md border border-amber-300 bg-amber-50 p-2 text-xs text-amber-800">
					<div class="mb-1 flex items-center gap-1 font-medium">
						<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
						{{ ctrans("Not in production here, or no quantity") }}
					</div>
					<div v-for="code in unmatchedCodes" :key="code" class="whitespace-nowrap font-mono">{{ code }}</div>
				</div>
			</div>

			<div class="flex min-w-0 flex-col gap-2">
				<InputText v-model="search" :placeholder="ctrans('Search code, name or purchase order')" />

				<div v-if="isLoading" class="space-y-2">
					<div v-for="index in 4" :key="index" class="h-12 animate-pulse rounded-lg bg-gray-100" />
				</div>

				<div v-else class="max-h-[50vh] overflow-y-auto rounded-lg border border-gray-200">
					<div
						v-for="line in filteredLines"
						:key="line.id"
						class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-gray-200 px-3 py-2 last:border-b-0"
						:class="(quantities[line.id] ?? 0) > 0 ? 'bg-[--app-accent-soft]' : ''"
					>
						<div class="min-w-0 flex-1 text-sm">
							<div class="font-medium text-gray-800">
								<span class="whitespace-nowrap">{{ line.code }}</span> <span class="font-normal text-gray-600">{{ line.name }}</span>
							</div>
							<div class="text-xs text-gray-500">
								<span class="whitespace-nowrap">{{ line.purchase_order_reference }}</span> · {{ line.supplier_code }} · {{ line.organisation_code }} ·
								{{ ctrans(":units units in production", { units: formatNumber(line.quantity) }) }}
								<span v-if="round(line.quantity) < round(line.quantity_ordered)">({{ ctrans("of :ordered ordered", { ordered: formatNumber(line.quantity_ordered) }) }})</span>
							</div>
							<div v-if="problem(line)" class="flex items-center gap-1 text-xs text-red-600">
								<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
								{{ problem(line) }}
							</div>
						</div>
						<div class="flex flex-col items-end gap-0.5">
							<div class="flex items-center gap-2">
								<InputNumber
									v-model="quantities[line.id]"
									:min="0"
									:maxFractionDigits="3"
									:suffix="` ${ctrans('units')}`"
									inputClass="!w-32 !py-1 text-right"
									:aria-label="ctrans('Units received of :code', { code: line.code })"
									@update:modelValue="serverErrors = {}"
								/>
								<Button type="tertiary" size="xs" :label="ctrans('All')" @click="quantities = { ...quantities, [line.id]: round(line.quantity) }" />
							</div>
							<span v-if="(quantities[line.id] ?? 0) > 0" class="text-xs tabular-nums text-gray-500">= {{ ctrans(":count SKO", { count: formatNumber((quantities[line.id] ?? 0) / unitsPerSko(line)) }) }}</span>
						</div>
					</div>

					<p v-if="filteredLines.length === 0" class="p-4 text-center text-sm text-gray-500">
						{{ lines.length === 0 ? ctrans("Nothing of this is in production") : ctrans("No lines match your search") }}
					</p>
				</div>

			</div>
		</div>

		<div v-if="scopeId" class="mt-4 grid gap-3 border-t border-gray-200 pt-4 sm:grid-cols-2">
			<label class="flex flex-col gap-1 text-sm">
				<span class="font-medium text-gray-700">{{ ctrans("Received on") }}</span>
				<DatePicker v-model="receivedAt" dateFormat="dd/mm/yy" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
			</label>
			<label class="flex flex-col gap-1 text-sm">
				<span class="font-medium text-gray-700">{{ ctrans("Supplier's delivery note number") }}</span>
				<InputText v-model="deliveryNote" maxlength="64" :class="fieldFocusClass" :placeholder="ctrans('Optional')" />
			</label>
			<div class="flex flex-col gap-1 text-sm sm:col-span-2">
				<span class="font-medium text-gray-700">{{ ctrans("Photo of the signed delivery note") }}</span>
				<div class="flex flex-wrap items-center gap-2">
					<FileUpload
						ref="photoUploader"
						mode="basic"
						:auto="false"
						customUpload
						multiple
						accept="image/*,.pdf"
						:maxFileSize="20000000"
						:chooseLabel="ctrans('Add photo')"
						:pt="chooseFilePt"
						@select="(event: { files: File[] }) => { photos = [...photos, ...event.files]; photoUploader?.clear() }"
					/>
					<span v-for="(photo, index) in photos" :key="photo.name + index" class="inline-flex items-center gap-1 rounded border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs text-gray-700">
						<span class="max-w-[12rem] truncate">{{ photo.name }}</span>
						<button type="button" class="text-gray-400 hover:text-red-500" :aria-label="ctrans('Remove :name', { name: photo.name })" @click="removePhoto(index)">
							<FontAwesomeIcon :icon="faTimes" fixed-width aria-hidden="true" />
						</button>
					</span>
					<span v-if="photos.length === 0" class="text-xs text-amber-600">{{ ctrans("You can add it later on the goods receipt") }}</span>
				</div>
			</div>
			<label class="flex flex-col gap-1 text-sm sm:col-span-2">
				<span class="font-medium text-gray-700">{{ ctrans("Note") }}</span>
				<InputText v-model="notes" maxlength="500" :class="fieldFocusClass" :placeholder="ctrans('Optional')" />
			</label>
		</div>

		<template #footer>
			<div class="flex w-full flex-wrap items-center justify-end gap-3">
				<span v-if="selectedLines.length" class="mr-auto text-sm text-gray-600">
					{{ ctrans(":lines lines · :units units", { lines: selectedLines.length, units: formatNumber(totalUnits) }) }}
				</span>
				<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button
					type="secondary"
					:label="selectedLines.length === 1 ? ctrans('Receive 1 line') : ctrans('Receive :count lines', { count: selectedLines.length })"
					:loading="isSubmitting"
					:disabled="selectedLines.length === 0 || hasProblem"
					@click="submit"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Dialog>
</template>

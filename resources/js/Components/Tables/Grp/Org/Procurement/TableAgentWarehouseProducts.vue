<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-15 15:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link, useForm } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faDolly, faBoxCheck, faBoxes, faTruckContainer, faInboxIn, faCheck, faTimes } from "@fal"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import Table from "@/Components/Table/Table.vue"
import Icon from "@/Components/Icon.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ZoomableImage from "@/Components/Utils/ZoomableImage.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

library.add(faDolly, faBoxCheck, faBoxes, faTruckContainer)

type ProductRow = {
	id: number | string
	code: string | null
	name: string | null
	quantity: number
	quantity_outstanding?: number
	units_per_sko: number
	receive_route?: routeType | null
	[key: string]: any
}

const props = defineProps<{
	data: { data?: ProductRow[] }
	kind: "in_production" | "received"
	tab?: string
	receiving?: { bulk_route: routeType } | null
}>()

const locale = useLocaleStore()
const organisation = route().params["organisation"]
const productRoute = (slug: string) => route("grp.org.agent.org_supplier_products.show", [organisation, slug])
const supplierRoute = (slug: string) => route("grp.org.agent.org_suppliers.show", [organisation, slug])
const purchaseOrderRoute = (slug: string) => route("grp.org.agent.purchase_orders.show", [organisation, slug])
const containerRoute = (slug: string) => route("grp.org.agent.stock_deliveries.show", [organisation, slug])

const formatQuantity = (value: number | null | undefined) => locale.number(Math.round(Number(value ?? 0) * 1000) / 1000)
const skos = (row: ProductRow, units: number | null | undefined) => formatQuantity(Number(units ?? 0) / (row.units_per_sko || 1))

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

function isoDate(date: Date | null) {
	if (!date) {
		return null
	}

	return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
}

const canReceive = computed(() => props.kind === "in_production" && !!props.receiving)

const tableRef = ref()
const selectedIds = ref<Record<string, boolean>>({})
const rows = computed(() => props.data?.data ?? [])
const selectedRows = computed(() => rows.value.filter((row) => selectedIds.value[row.id] && row.quantity > 0))
const selectedUnits = computed(() => selectedRows.value.reduce((sum, row) => sum + Number(row.quantity), 0))

const receivingRow = ref<ProductRow | null>(null)
const receiveForm = useForm<{ received_at: Date | null; quantity_received: number | null; notes: string | null }>({
	received_at: new Date(),
	quantity_received: null,
	notes: null,
})

function openReceive(row: ProductRow) {
	receivingRow.value = row
	receiveForm.clearErrors()
	receiveForm.received_at = new Date()
	receiveForm.quantity_received = row.quantity
	receiveForm.notes = null
}

function submitReceive() {
	const receiveRoute = receivingRow.value?.receive_route
	if (!receiveRoute) {
		return
	}

	receiveForm
		.transform((data) => ({ ...data, received_at: isoDate(data.received_at), notes: data.notes || null }))
		.post(route(receiveRoute.name, receiveRoute.parameters), {
			preserveScroll: true,
			onSuccess: () => (receivingRow.value = null),
		})
}

const isBulkReceiveOpen = ref(false)
const bulkNotes = ref<Record<string, string>>({})
const bulkForm = useForm<{ received_at: Date | null }>({
	received_at: new Date(),
})

function openBulkReceive() {
	bulkForm.clearErrors()
	bulkForm.received_at = new Date()
	bulkNotes.value = {}
	isBulkReceiveOpen.value = true
}

function submitBulkReceive() {
	if (!props.receiving) {
		return
	}

	bulkForm
		.transform((data) => ({
			received_at: isoDate(data.received_at),
			lines: selectedRows.value.map((row) => ({ purchase_order_transaction_id: row.id, quantity_received: row.quantity, notes: bulkNotes.value[row.id] || null })),
		}))
		.post(route(props.receiving.bulk_route.name, props.receiving.bulk_route.parameters), {
			preserveScroll: true,
			onSuccess: () => {
				isBulkReceiveOpen.value = false
				tableRef.value?.clearSelection()
			},
		})
}
</script>

<template>
	<div>
	<Table
		ref="tableRef"
		:resource="data"
		:name="tab"
		class="mt-5"
		:isCheckBox="canReceive"
		@onSelectRow="(value) => (selectedIds = { ...value })"
	>
		<template v-if="canReceive" #before-table>
			<div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-4 py-2 text-sm">
				<span v-if="selectedRows.length" class="text-gray-700">
					{{ ctrans(":count selected, :units units", { count: selectedRows.length, units: formatQuantity(selectedUnits) }) }}
				</span>
				<span v-else class="text-gray-500">{{ ctrans("Tick the products that arrived in full to receive them together") }}</span>
				<Button type="tertiary" size="xs" :label="ctrans('Receive selected in full')" :disabled="!selectedRows.length" @click="openBulkReceive">
					<template #icon>
						<FontAwesomeIcon :icon="faInboxIn" class="text-[--app-accent]" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
		<template #cell(location)="{ item: row }">
			<div class="flex items-center gap-1.5">
				<Icon :data="row.location_icon" />
				<Link v-if="row.container_slug" :href="containerRoute(row.container_slug)" class="secondaryLink">{{ row.container_reference }}</Link>
				<span v-else class="text-gray-500">{{ ctrans("Warehouse") }}</span>
				<span
					v-if="row.is_unchecked"
					v-tooltip="ctrans('Received, not checked for rejects yet')"
					class="whitespace-nowrap rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700 ring-1 ring-inset ring-amber-200"
				>
					{{ ctrans("Not checked yet") }}
				</span>
			</div>
		</template>
		<template #cell(image_thumbnail)="{ item: row }">
			<ZoomableImage :src="row.image_thumbnail" :full="row.image_full" :alt="row.code" class="h-12 w-12" />
		</template>
		<template #cell(code)="{ item: row }">
			<Link v-if="row.org_supplier_product_slug" :href="productRoute(row.org_supplier_product_slug)" class="primaryLink">{{ row.code }}</Link>
			<span v-else>{{ row.code }}</span>
		</template>
		<template #cell(supplier_code)="{ item: row }">
			<Link v-if="row.org_supplier_slug" :href="supplierRoute(row.org_supplier_slug)" class="secondaryLink">{{ row.supplier_code }}</Link>
			<span v-else>{{ row.supplier_code }}</span>
		</template>
		<template #cell(purchase_order_reference)="{ item: row }">
			<Link v-if="row.purchase_order_slug" :href="purchaseOrderRoute(row.purchase_order_slug)" class="secondaryLink">{{ row.purchase_order_reference }}</Link>
		</template>
		<template #cell(production_started_at)="{ item: row }">
			<span v-if="row.production_started_at" class="whitespace-nowrap">{{ useFormatTime(row.production_started_at) }}</span>
		</template>
		<template #cell(ready_at)="{ item: row }">
			<span v-if="row.ready_at" class="whitespace-nowrap" v-tooltip="row.ready_at_is_approved ? ctrans('Approved ready date') : ctrans('Ready date proposed by the supplier, not approved yet')">
				{{ useFormatTime(row.ready_at) }}<span v-if="!row.ready_at_is_approved" class="text-gray-400"> ({{ ctrans("proposed") }})</span>
			</span>
		</template>
		<template #cell(quantity)="{ item: row }">
			<div class="flex items-center justify-end gap-1.5">
				<span
					v-if="row.quantity_rejected > 0"
					v-tooltip="ctrans(':units units were rejected at our warehouse: the supplier makes them again', { units: formatQuantity(row.quantity_rejected) })"
					class="whitespace-nowrap rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-700 ring-1 ring-inset ring-red-200"
				>
					{{ ctrans(":units rejected", { units: formatQuantity(row.quantity_rejected) }) }}
				</span>
				<span v-if="row.quantity_ordered && row.quantity_ordered > row.quantity" class="tabular-nums" v-tooltip="ctrans(':received of :ordered units ordered already received', { received: formatQuantity(row.quantity_ordered - row.quantity), ordered: formatQuantity(row.quantity_ordered) })">
					{{ formatQuantity(row.quantity) }} <span class="text-gray-400">/ {{ formatQuantity(row.quantity_ordered) }}</span>
				</span>
				<span v-else class="tabular-nums">{{ formatQuantity(row.quantity) }}</span>
			</div>
		</template>
		<template #cell(skos)="{ item: row }">
			<span class="tabular-nums" v-tooltip="ctrans('1 SKO = :units units', { units: formatQuantity(row.units_per_sko) })">{{ formatQuantity(row.skos) }}</span>
		</template>
		<template #cell(actions)="{ item: row }">
			<Button v-if="row.receive_route" type="tertiary" size="xs" :label="ctrans('Receive')" @click="openReceive(row)">
				<template #icon>
					<FontAwesomeIcon :icon="faInboxIn" class="text-[--app-accent]" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Table>

	<Dialog
		:visible="!!receivingRow"
		modal
		:header="ctrans('Receive :code', { code: receivingRow?.code ?? '' })"
		:style="{ width: '28rem' }"
		:breakpoints="{ '640px': '95vw' }"
		@update:visible="(visible) => !visible && (receivingRow = null)"
	>
		<div v-if="receivingRow" class="space-y-3 text-sm">
			<p class="text-gray-600">{{ receivingRow.purchase_order_reference }} · {{ receivingRow.name }}</p>
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Date received") }}</span>
				<DatePicker v-model="receiveForm.received_at" dateFormat="yy-mm-dd" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" fluid />
			</label>
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Units received") }}</span>
				<InputNumber v-model="receiveForm.quantity_received" :min="0" :max="receivingRow.quantity_outstanding" :maxFractionDigits="3" :class="fieldFocusClass" fluid />
				<span class="block text-xs text-gray-500">
					{{ ctrans("= :skos SKO. Up to :units units are still to come.", { skos: skos(receivingRow, receiveForm.quantity_received), units: formatQuantity(receivingRow.quantity_outstanding) }) }}
				</span>
			</label>
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Remarks (optional)") }}</span>
				<Textarea v-model="receiveForm.notes" rows="2" autoResize fluid :placeholder="ctrans('Printed on the receipt note for the supplier')" />
			</label>
			<div v-for="(error, field) in receiveForm.errors" :key="field" class="text-xs text-red-600">{{ error }}</div>
			<div class="flex justify-end gap-2">
				<Button type="tertiary" :label="ctrans('Cancel')" @click="receivingRow = null">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" :label="ctrans('Save')" :loading="receiveForm.processing" :disabled="!receiveForm.quantity_received || !receiveForm.received_at" @click="submitReceive">
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</div>
	</Dialog>

	<Dialog
		v-model:visible="isBulkReceiveOpen"
		modal
		:header="ctrans('Receive :count products in full', { count: selectedRows.length })"
		:style="{ width: '34rem' }"
		:breakpoints="{ '640px': '95vw' }"
	>
		<div class="space-y-3 text-sm">
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Date received") }}</span>
				<DatePicker v-model="bulkForm.received_at" dateFormat="yy-mm-dd" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" fluid />
			</label>
			<div class="max-h-[50vh] divide-y divide-gray-100 overflow-y-auto rounded-md border border-gray-200">
				<div v-for="row in selectedRows" :key="row.id" class="space-y-1.5 px-3 py-2">
					<div class="flex items-center gap-3">
						<ZoomableImage :src="row.image_thumbnail" :full="row.image_full" :alt="row.code" class="h-10 w-10" />
						<div class="min-w-0 flex-1">
							<div class="font-medium text-gray-800">{{ row.code }}</div>
							<div class="truncate text-xs text-gray-500">{{ row.purchase_order_reference }} · {{ row.name }}</div>
						</div>
						<div class="text-right tabular-nums">
							<div class="font-medium text-gray-800">{{ ctrans(":units units", { units: formatQuantity(row.quantity) }) }}</div>
							<div class="text-xs text-gray-500">{{ skos(row, row.quantity) }} SKO</div>
						</div>
					</div>
					<InputText v-model="bulkNotes[row.id]" size="small" :class="fieldFocusClass" :placeholder="ctrans('Remarks (optional)')" fluid />
				</div>
			</div>
			<div class="rounded-md border border-[--app-accent-muted] bg-[--app-accent-soft] p-3 text-xs text-gray-700">
				<div class="mb-1 font-medium text-gray-800">{{ ctrans("What happens next") }}</div>
				<ol class="list-decimal space-y-1 pl-4">
					<li>{{ ctrans("Each product above is recorded as arrived on this date, with the units shown, and leaves In Production.") }}</li>
					<li>{{ ctrans("It moves to Received, marked Not checked yet.") }}</li>
					<li>{{ ctrans("Then check the goods for rejects: open the purchase order and press Check on the batch in Received at our warehouse. Rejected units come back to In Production for the supplier to replace.") }}</li>
				</ol>
				<div class="mt-2 text-gray-500">{{ ctrans("Only part of a product arrived? Close this and use Receive on its row to enter the units that came.") }}</div>
			</div>
			<div v-for="(error, field) in bulkForm.errors" :key="field" class="text-xs text-red-600">{{ error }}</div>
			<div class="flex justify-end gap-2">
				<Button type="tertiary" :label="ctrans('Cancel')" @click="isBulkReceiveOpen = false">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" :label="ctrans('Receive all')" :loading="bulkForm.processing" :disabled="!selectedRows.length || !bulkForm.received_at" @click="submitBulkReceive">
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</div>
	</Dialog>
	</div>
</template>

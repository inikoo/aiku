<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 09 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import DatePicker from "primevue/datepicker"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import FileUpload from "primevue/fileupload"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Select from "primevue/select"
import { InvoiceChargeType, invoiceChargeTypeOptions } from "@/Components/Procurement/invoiceChargeTypes"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFileInvoice, faPlus, faTrashAlt, faTimes, faCheck } from "@fal"

library.add(faFileInvoice, faPlus, faTrashAlt, faTimes, faCheck)

interface Charge {
	description: string
	type: InvoiceChargeType
	amount: number | null
}

const props = defineProps<{
	route: routeType
	currencyCode: string
	isAgent: boolean
	invoice: {
		source: "agent" | "actual" | "estimated"
		reference: string | null
		date: string
		goods: number
		charges_list: { description: string; type?: InvoiceChargeType; amount: number }[]
	} | null
}>()

const locale = useLocaleStore()
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const isSaving = ref(false)
const errors = ref<Record<string, string>>({})

const reference = ref("")
const date = ref<Date | null>(null)
const goodsAmount = ref<number | null>(null)
const charges = ref<Charge[]>([])
const files = ref<File[]>([])

const chargeTypes = invoiceChargeTypeOptions()

const isActual = computed(() => props.invoice?.source === "actual")

const toIsoDate = (value: Date) => `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, "0")}-${String(value.getDate()).padStart(2, "0")}`

const reset = () => {
	const fromActual = isActual.value && props.invoice
	reference.value = fromActual ? props.invoice!.reference ?? "" : ""
	date.value = fromActual ? new Date(props.invoice!.date) : null
	goodsAmount.value = props.invoice ? props.invoice.goods : null
	charges.value = (props.invoice?.charges_list ?? []).map(charge => ({ description: charge.description, type: charge.type ?? "other", amount: charge.amount }))
	files.value = []
	errors.value = {}
}

reset()
watch(() => props.invoice, reset)

const total = computed(() => (goodsAmount.value ?? 0) + charges.value.reduce((sum, charge) => sum + (charge.amount ?? 0), 0))

const save = () => {
	router.post(
		route(props.route.name, props.route.parameters),
		{
			reference: reference.value,
			date: date.value ? toIsoDate(date.value) : null,
			goods_amount: goodsAmount.value,
			charges: charges.value,
			...(files.value.length ? { attachments: files.value } : {}),
		},
		{
			preserveScroll: true,
			preserveState: true,
			forceFormData: files.value.length > 0,
			onStart: () => { isSaving.value = true },
			onFinish: () => { isSaving.value = false },
			onSuccess: () => notify({ title: ctrans("Invoice saved"), type: "success" }),
			onError: (formErrors) => { errors.value = formErrors },
		}
	)
}
</script>

<template>
	<div class="px-4 py-3">
		<div class="overflow-hidden rounded-md border border-gray-200">
			<div class="bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
				{{ isActual ? ctrans("The invoice") : props.invoice?.source === "estimated" ? ctrans("Enter the real invoice") : ctrans("Enter the invoice") }}
			</div>
			<div class="p-3">
			<p v-if="props.invoice?.source === 'estimated'" class="mb-4 text-sm text-gray-500">
				{{ ctrans("These figures replace the estimate. The estimate is kept for comparison.") }}
			</p>

			<div class="grid grid-cols-2 gap-4">
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Invoice number") }}</span>
					<InputText v-model="reference" class="mt-1 w-full" :class="fieldFocusClass" />
					<span v-if="errors.reference" class="text-xs text-red-600">{{ errors.reference }}</span>
				</label>
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Invoice date") }}</span>
					<DatePicker v-model="date" dateFormat="dd/mm/yy" showIcon iconDisplay="input" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
					<span v-if="errors.date" class="text-xs text-red-600">{{ errors.date }}</span>
				</label>
				<label class="col-span-2 block text-sm">
					<span class="text-gray-600">{{ ctrans("Goods") }} ({{ currencyCode }})</span>
					<InputNumber v-model="goodsAmount" mode="currency" :currency="currencyCode" :min="0" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
					<span v-if="errors.goods_amount" class="text-xs text-red-600">{{ errors.goods_amount }}</span>
				</label>
			</div>

			<div class="mt-5 text-sm">
				<div class="mb-2 text-gray-600">{{ isAgent ? ctrans("Agent charges") : ctrans("Supplier charges") }}</div>
				<div v-for="(charge, index) in charges" :key="index" class="mb-2 flex items-center gap-2">
					<InputText v-model="charge.description" :placeholder="ctrans('Description')" size="small" class="flex-1" :class="fieldFocusClass" />
					<Select v-model="charge.type" :options="chargeTypes" optionLabel="label" optionValue="value" class="w-56" :aria-label="ctrans('Charge type')" />
					<InputNumber v-model="charge.amount" mode="currency" :currency="currencyCode" :min="0" size="small" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-32 !text-right' } } }" :aria-label="ctrans('Amount')" />
					<Button type="negative" size="xs" icon="fal fa-trash-alt" :aria-label="ctrans('Remove')" @click="charges.splice(index, 1)" />
				</div>
				<Button type="tertiary" size="xs" icon="fal fa-plus" :label="ctrans('Add charge')" @click="charges.push({ description: '', type: 'other', amount: null })" />
				<span v-if="Object.keys(errors).some(key => key.startsWith('charges'))" class="mt-1 block text-xs text-red-600">{{ ctrans("Every charge needs a description and an amount") }}</span>
			</div>

			<div class="mt-5 flex items-center justify-between border-t border-gray-200 pt-3 text-sm font-semibold text-gray-700">
				<span>{{ ctrans("Total") }}</span>
				<span>{{ locale.currencyFormat(currencyCode, total) }}</span>
			</div>
			<span v-if="errors.invoice" class="mt-2 block text-sm text-red-600">{{ errors.invoice }}</span>

			<div class="mt-4 text-sm">
				<span class="text-gray-600">{{ ctrans("Invoice file") }}</span>
				<div class="mt-1 flex flex-wrap items-center gap-2">
					<FileUpload mode="basic" :auto="false" customUpload multiple accept=".pdf,image/*,.xlsx,.xls,.csv" :maxFileSize="50000000" :chooseLabel="ctrans('Choose file')" @select="(event: { files: File[] }) => (files = event.files)" />
					<span class="text-xs text-gray-500">{{ ctrans("PDF, Excel or image of the invoice, kept with the delivery") }}</span>
				</div>
				<span v-if="Object.keys(errors).some(key => key.startsWith('attachments'))" class="mt-1 block text-xs text-red-600">{{ ctrans("The file could not be uploaded") }}</span>
			</div>

			<div class="mt-4 flex justify-end gap-2">
				<Button type="tertiary" :label="ctrans('Save invoice')" :loading="isSaving" @click="save">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
			</div>
		</div>
	</div>
</template>

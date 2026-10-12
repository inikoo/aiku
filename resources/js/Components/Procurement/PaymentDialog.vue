<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 10:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, computed, watch } from "vue"
import Dialog from "primevue/dialog"
import DatePicker from "primevue/datepicker"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Select from "primevue/select"
import Textarea from "primevue/textarea"
import FileUpload from "primevue/fileupload"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTimes, faCheck } from "@fal"
import { ctrans } from "@/Composables/useTrans"

library.add(faTimes, faCheck)

export interface PaymentPayload {
	date: string
	amount: number
	method: string
	reference: string | null
	notes: string | null
	bank_statement: File | null
}

const props = defineProps<{
	title: string
	currency: string
	methods: { name: string; code: string }[]
	defaultAmount?: number | null
	withNotes?: boolean
	loading?: boolean
}>()

const visible = defineModel<boolean>("visible", { required: true })

const emit = defineEmits<{ submit: [payload: PaymentPayload] }>()

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent] focus:!border-[--app-accent]"
const dialogPt = { header: { class: "!px-4 !py-2.5 border-b border-gray-200" }, content: { class: "!p-4" }, footer: { class: "!px-4 !pb-4" } }
const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const emptyForm = () => ({
	date: new Date() as Date | null,
	amount: props.defaultAmount && props.defaultAmount > 0 ? props.defaultAmount : (null as number | null),
	method: "bank_transfer",
	reference: "",
	notes: "",
	file: null as File | null,
})
const form = ref(emptyForm())

watch(visible, (isVisible) => {
	if (isVisible) form.value = emptyForm()
})

const needsStatement = computed(() => form.value.method !== "cash")
const canSubmit = computed(() => !!form.value.date && !!form.value.amount && (!needsStatement.value || !!form.value.file))

const toIsoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const submit = () => {
	if (!canSubmit.value) return
	emit("submit", {
		date: toIsoDate(form.value.date as Date),
		amount: form.value.amount as number,
		method: form.value.method,
		reference: form.value.reference || null,
		notes: form.value.notes || null,
		bank_statement: form.value.file,
	})
}
</script>

<template>
	<Dialog v-model:visible="visible" modal dismissableMask :closable="false" :pt="dialogPt" class="w-full max-w-md">
		<template #header>
			<div class="flex w-full items-center justify-between gap-3">
				<span class="text-sm font-semibold text-gray-900">{{ title }}</span>
				<button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="visible = false">
					<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
				</button>
			</div>
		</template>
		<div class="space-y-3">
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Amount") }}</span>
				<InputNumber v-model="form.amount" mode="currency" :currency="currency" :min="0" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Date the payment was sent") }}</span>
				<DatePicker v-model="form.date" dateFormat="dd/mm/yy" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Payment method") }}</span>
				<Select v-model="form.method" :options="methods" optionLabel="name" optionValue="code" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Payment reference") }}</span>
				<InputText v-model="form.reference" maxlength="255" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label v-if="withNotes" class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Notes") }}</span>
				<Textarea v-model="form.notes" rows="2" class="mt-1 w-full" :class="fieldFocusClass" autoResize />
			</label>
			<div class="block text-sm">
				<span class="text-gray-600">{{ needsStatement ? ctrans("Bank statement") : ctrans("Receipt (optional)") }}</span>
				<div class="mt-1 flex flex-wrap items-center gap-2">
					<FileUpload mode="basic" :auto="false" customUpload accept=".pdf,image/*" :maxFileSize="20000000" :chooseLabel="ctrans('Choose file')" :pt="chooseFilePt" @select="(event: { files: File[] }) => (form.file = event.files[0] ?? null)" />
				</div>
			</div>
		</div>
		<template #footer>
			<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Record payment')" :disabled="!canSubmit" :loading="loading" @click="submit">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { Head, useForm, router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import DatePicker from "primevue/datepicker"
import InputText from "primevue/inputtext"
import Checkbox from "primevue/checkbox"
import { useConfirm } from "primevue/useconfirm"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt, faPencil, faCheck, faTimes, faPlus } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import HrConfirmDialog from "@/Components/HumanResources/HrConfirmDialog.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { isoToDate, dateToIso, fieldFocusClass, inputFocusClass, checkboxAccentClass } from "@/Composables/useHrFormControls"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faTrashAlt, faPencil, faCheck, faTimes, faPlus)

interface RestrictedPeriod {
	id: number
	label: string | null
	start_date: string | null
	end_date: string | null
	strictness: string | null
	is_active: boolean
	allow_superuser_override: boolean
}

const props = defineProps<{
	pageHead: PageHeadingTypes
	title: string
	data: any
	strictnessOptions: { value: string; label: string }[]
}>()

const confirm = useConfirm()
const confirmGroup = "restricted-periods"

const showDialog = ref(false)
const isEditMode = ref(false)
const editingRestrictedPeriodId = ref<number | null>(null)

const form = useForm<{
	label: string
	start_date: string
	end_date: string
	strictness: string
	is_active: boolean
	allow_superuser_override: boolean
}>({
	label: "",
	start_date: "",
	end_date: "",
	strictness: "block",
	is_active: true,
	allow_superuser_override: true,
})

const dateOnly = (value: string | null | undefined): string => (value ?? "").slice(0, 10)

const dateModel = (key: "start_date" | "end_date") =>
	computed<Date | null>({
		get: () => isoToDate(form[key]),
		set: (value) => {
			form[key] = dateToIso(value)
			form.clearErrors(key)
		},
	})

const startDate = dateModel("start_date")
const endDate = dateModel("end_date")

const resetForm = () => {
	form.reset()
	form.clearErrors()
	isEditMode.value = false
	editingRestrictedPeriodId.value = null
}

const openCreate = () => {
	resetForm()
	showDialog.value = true
}

const openEdit = (row: RestrictedPeriod) => {
	form.reset()
	form.clearErrors()
	isEditMode.value = true
	editingRestrictedPeriodId.value = row.id ?? null

	form.label = row.label ?? ""
	form.start_date = dateOnly(row.start_date)
	form.end_date = dateOnly(row.end_date)
	form.strictness = row.strictness ?? "block"
	form.is_active = Boolean(row.is_active)
	form.allow_superuser_override = Boolean(row.allow_superuser_override)

	showDialog.value = true
}

const closeDialog = () => {
	showDialog.value = false
}

const submit = () => {
	const options = {
		preserveScroll: true,
		onSuccess: () => {
			showDialog.value = false
			resetForm()
		},
	}

	if (isEditMode.value && editingRestrictedPeriodId.value) {
		form.patch(
			route("grp.org.hr.restricted_periods.update", {
				...route().params,
				restrictedPeriod: editingRestrictedPeriodId.value,
			}),
			options
		)

		return
	}

	form.post(route("grp.org.hr.restricted_periods.store", route().params), options)
}

const confirmDelete = (row: RestrictedPeriod) => {
	confirm.require({
		group: confirmGroup,
		header: ctrans("Delete restricted period"),
		message: ctrans("Are you sure you want to delete :name? This can not be undone.", { name: row.label || ctrans("this restricted period") }),
		rejectProps: { label: ctrans("Cancel") },
		acceptProps: { label: ctrans("Delete"), severity: "danger" },
		accept: () => {
			router.delete(
				route("grp.org.hr.restricted_periods.delete", {
					...route().params,
					restrictedPeriod: row.id,
				}),
				{ preserveScroll: true }
			)
		},
	})
}

const dialogTitle = computed(() =>
	isEditMode.value ? ctrans("Edit restricted period") : ctrans("Create restricted period")
)

const strictnessLabel = (strictness: string | null): string =>
	props.strictnessOptions.find((option) => option.value === strictness)?.label ?? strictness ?? "—"

const strictnessTagClass = (strictness: string | null): string => {
	if (strictness === "block") {
		return "bg-red-100 text-red-800"
	}

	if (strictness === "warn") {
		return "bg-amber-100 text-amber-800"
	}

	return "bg-gray-100 text-gray-700"
}

const tagClass = "inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium"
</script>

<template>
	<Head :title="capitalize(title)" />

	<PageHeading :data="pageHead">
		<template #button-restricted-period="{ action }">
			<Button type="create" :icon="action.icon" :label="action.label" @click="openCreate" />
		</template>
	</PageHeading>

	<Table :resource="data" class="mt-5">
		<template #cell(start_date)="{ item }">
			<span class="whitespace-nowrap tabular-nums">{{ dateOnly(item.start_date) }}</span>
		</template>

		<template #cell(end_date)="{ item }">
			<span class="whitespace-nowrap tabular-nums">{{ dateOnly(item.end_date) }}</span>
		</template>

		<template #cell(strictness)="{ item }">
			<span :class="[tagClass, strictnessTagClass(item.strictness)]">
				{{ strictnessLabel(item.strictness) }}
			</span>
		</template>

		<template #cell(is_active)="{ item }">
			<span :class="[tagClass, item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600']">
				{{ item.is_active ? ctrans("Active") : ctrans("Inactive") }}
			</span>
		</template>

		<template #cell(allow_superuser_override)="{ item }">
			<span :class="[tagClass, item.allow_superuser_override ? 'bg-sky-100 text-sky-800' : 'bg-gray-100 text-gray-600']">
				{{ item.allow_superuser_override ? ctrans("Allowed") : ctrans("Not allowed") }}
			</span>
		</template>

		<template #cell(action)="{ item }">
			<div class="flex justify-end gap-2">
				<Button type="tertiary" size="xs" :label="ctrans('Edit')" @click="openEdit(item)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-pencil" class="text-[--app-accent]" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" size="xs" :label="ctrans('Delete')" @click="confirmDelete(item)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-trash-alt" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Table>

	<Dialog
		v-model:visible="showDialog"
		modal
		:draggable="false"
		:header="dialogTitle"
		:style="{ width: '36rem', maxWidth: 'calc(100vw - 2rem)' }"
		@hide="resetForm">
		<form class="space-y-4" @submit.prevent="submit">
			<div>
				<label for="restricted-period-label" class="mb-1 block text-sm font-medium text-gray-700">
					{{ ctrans("Label") }}
				</label>
				<InputText
					v-model="form.label"
					id="restricted-period-label"
					:invalid="!!form.errors.label"
					fluid
					:class="inputFocusClass" />
				<p v-if="form.errors.label" class="mt-1 text-sm text-red-500">
					{{ form.errors.label }}
				</p>
			</div>

			<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
				<div>
					<label for="restricted-period-start" class="mb-1 block text-sm font-medium text-gray-700">
						{{ ctrans("Start Date") }}
					</label>
					<DatePicker
						v-model="startDate"
						inputId="restricted-period-start"
						dateFormat="d M yy"
						:manualInput="false"
						:invalid="!!form.errors.start_date"
						showIcon
						iconDisplay="input"
						fluid
						:class="fieldFocusClass" />
					<p v-if="form.errors.start_date" class="mt-1 text-sm text-red-500">
						{{ form.errors.start_date }}
					</p>
				</div>

				<div>
					<label for="restricted-period-end" class="mb-1 block text-sm font-medium text-gray-700">
						{{ ctrans("End Date") }}
					</label>
					<DatePicker
						v-model="endDate"
						inputId="restricted-period-end"
						dateFormat="d M yy"
						:minDate="startDate ?? undefined"
						:manualInput="false"
						:invalid="!!form.errors.end_date"
						showIcon
						iconDisplay="input"
						fluid
						:class="fieldFocusClass" />
					<p v-if="form.errors.end_date" class="mt-1 text-sm text-red-500">
						{{ form.errors.end_date }}
					</p>
				</div>
			</div>

			<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
				<div>
					<label for="restricted-period-strictness" class="mb-1 block text-sm font-medium text-gray-700">
						{{ ctrans("Strictness") }}
					</label>
					<Select
						v-model="form.strictness"
						inputId="restricted-period-strictness"
						:options="strictnessOptions"
						optionLabel="label"
						optionValue="value"
						:invalid="!!form.errors.strictness"
						fluid
						:class="fieldFocusClass" />
					<p v-if="form.errors.strictness" class="mt-1 text-sm text-red-500">
						{{ form.errors.strictness }}
					</p>
				</div>
			</div>

			<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
				<div class="flex items-center gap-2">
					<Checkbox v-model="form.is_active" inputId="restricted-period-is-active" binary :class="checkboxAccentClass" />
					<label for="restricted-period-is-active" class="cursor-pointer text-sm text-gray-700">
						{{ ctrans("Active") }}
					</label>
				</div>

				<div class="flex items-center gap-2">
					<Checkbox
						v-model="form.allow_superuser_override"
						inputId="restricted-period-superuser-override"
						binary
						:class="checkboxAccentClass" />
					<label for="restricted-period-superuser-override" class="cursor-pointer text-sm text-gray-700">
						{{ ctrans("Allow Superuser Override") }}
					</label>
				</div>
			</div>

			<div class="flex justify-end gap-2 pt-2">
				<Button type="tertiary" :label="ctrans('Cancel')" @click="closeDialog">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" nativeType="submit" :label="ctrans('Save')" :loading="form.processing">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</form>
	</Dialog>

	<HrConfirmDialog :group="confirmGroup" />
</template>

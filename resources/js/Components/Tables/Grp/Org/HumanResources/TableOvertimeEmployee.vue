<script setup lang="ts">
import { useForm, router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Tag from "@/Components/Tag.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import Dialog from "primevue/dialog"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { ref, computed } from "vue"
import Select from "primevue/select"
import DatePicker from "primevue/datepicker"
import Textarea from "primevue/textarea"
import OvertimeTimeFields from "@/Components/HumanResources/OvertimeTimeFields.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrash, faEdit, faCheck, faTimes, faTachometerAlt, faList, faLayerGroup, faPlus } from "@fal";

library.add(faTrash, faEdit, faCheck, faTimes, faTachometerAlt, faList, faLayerGroup, faPlus)

const props = defineProps<{
	data: {}
	tab?: string
	organisation?: string | null
	overtimeTypeOptions?: { value: number; label: string }[]
	isRequestOvertimeModalOpen?: boolean
}>()

const emit = defineEmits<{
	(e: "update:isRequestOvertimeModalOpen", value: boolean): void
}>()

const isEditMode = ref(false)
const editingOvertimeId = ref<number | null>(null)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const getOvertimeError = (key: string): string | undefined => {
	return (overtimeForm.errors as Record<string, string | undefined>)[key]
}

const overtimeForm = useForm<{
	overtime_type_id: number | null
	requested_date: string
	start_hour: string
	start_minute: string
	duration_hours: string
	duration_minutes: string
	reason: string
}>({
	overtime_type_id: null,
	requested_date: "",
	start_hour: "00",
	start_minute: "00",
	duration_hours: "0",
	duration_minutes: "0",
	reason: "",
})

const overtimeTypeSelectOptions = computed(() => props.overtimeTypeOptions ?? [])

const formatDuration = (minutes?: number | null): string => {
	if (!minutes) {
		return "-"
	}

	const hours = Math.floor(minutes / 60)
	const remainingMinutes = minutes % 60

	if (hours && remainingMinutes) {
		return `${hours}h ${remainingMinutes}m`
	}

	if (hours) {
		return `${hours}h`
	}

	return `${remainingMinutes}m`
}

const extractTimeParts = (value?: string | null): { hour: string; minute: string } => {
	if (!value) {
		return { hour: "00", minute: "00" }
	}

	const date = new Date(value)

	if (!Number.isNaN(date.getTime())) {
		const hour = date.getHours()
		const minute = date.getMinutes()
		return {
			hour: hour < 10 ? `0${hour}` : String(hour),
			minute: minute < 10 ? `0${minute}` : String(minute),
		}
	}

	const timePart = value.split(" ")[1] ?? value
	const [hour, minute] = timePart.split(":")

	return {
		hour: hour?.padStart(2, "0") ?? "00",
		minute: minute?.padStart(2, "0") ?? "00",
	}
}

const getFullDate = (dateStr: string, hour: string, minute: string) => {
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d, Number(hour), Number(minute));
}

const toLocalISOString = (date: Date) => {
    const tzo = -date.getTimezoneOffset(),
        dif = tzo >= 0 ? '+' : '-',
        pad = (num: number) => {
            const norm = Math.floor(Math.abs(num));
            return (norm < 10 ? '0' : '') + norm;
        };
    return date.getFullYear() +
        '-' + pad(date.getMonth() + 1) +
        '-' + pad(date.getDate()) +
        'T' + pad(date.getHours()) +
        ':' + pad(date.getMinutes()) +
        ':' + pad(date.getSeconds()) +
        dif + pad(tzo / 60) + ':' + pad(tzo % 60);
}

const parseYmdDate = (value: string): Date | null => {
	if (!value) {
		return null
	}

	const [year, month, day] = value.split("-").map(Number)
	if (!year || !month || !day) {
		return null
	}

	return new Date(year, month - 1, day)
}

const formatYmdDate = (date: Date | null): string => {
	if (!date) {
		return ""
	}

	const year = date.getFullYear()
	const month = String(date.getMonth() + 1).padStart(2, "0")
	const day = String(date.getDate()).padStart(2, "0")

	return `${year}-${month}-${day}`
}

const overtimeRequestedDateModel = computed<Date | null>({
	get: () => parseYmdDate(overtimeForm.requested_date),
	set: (value) => {
		overtimeForm.requested_date = formatYmdDate(value)
	},
})

const resetFormState = () => {
	overtimeForm.reset()
	overtimeForm.clearErrors()
	overtimeForm.start_hour = "00"
	overtimeForm.start_minute = "00"
	overtimeForm.duration_hours = "0"
	overtimeForm.duration_minutes = "0"
}

const openModal = () => {
	isEditMode.value = false
	editingOvertimeId.value = null
	resetFormState()
	emit("update:isRequestOvertimeModalOpen", true)
}

defineExpose({ openModal })

const openEditModal = (item: any) => {
	isEditMode.value = true
	editingOvertimeId.value = item.id

	resetFormState()
	overtimeForm.clearErrors()

	overtimeForm.overtime_type_id = item.overtime_type_id ?? null
	overtimeForm.requested_date = item.requested_date ? useFormatTime(item.requested_date, { formatTime: 'yyyy-MM-dd' }) : ''

	const startAt: string | null = item.requested_start_at ?? null
    if (startAt) {
        overtimeForm.start_hour = useFormatTime(startAt, { formatTime: 'HH' })
        overtimeForm.start_minute = useFormatTime(startAt, { formatTime: 'mm' })
    }

	const durationMinutes = item.requested_duration_minutes ?? 0
	const hours = Math.floor(durationMinutes / 60)
	const minutes = durationMinutes % 60
	overtimeForm.duration_hours = String(hours)
	overtimeForm.duration_minutes = String(minutes)

	overtimeForm.reason = item.reason ?? ""
	emit("update:isRequestOvertimeModalOpen", true)
}

const closeModal = () => {
	emit("update:isRequestOvertimeModalOpen", false)
	isEditMode.value = false
	editingOvertimeId.value = null
	resetFormState()
}

const submitOvertimeRequest = () => {
	overtimeForm
		.transform((data) => {
            const requestedStartAt = getFullDate(data.requested_date, data.start_hour, data.start_minute)
            const requestedDurationMinutes = (Number(data.duration_hours) * 60) + Number(data.duration_minutes)
            const requestedEndAt = new Date(requestedStartAt.getTime() + requestedDurationMinutes * 60000)

            return {
                ...data,
                organisation: props.organisation ?? undefined,
                requested_start_at: toLocalISOString(requestedStartAt),
                requested_end_at: toLocalISOString(requestedEndAt),
                requested_duration_minutes: requestedDurationMinutes,
            }
        })
		.post(
			isEditMode.value && editingOvertimeId.value
				? route("grp.clocking_employees.overtime_requests.update", {
						overtimeRequest: editingOvertimeId.value,
				  })
				: route("grp.clocking_employees.overtime_requests.store"),
			{
				preserveScroll: true,
				onSuccess: () => {
					closeModal()
				},
			}
		)
}
</script>

<template>
	<div class="mt-4 space-y-4">

		<Table :resource="data" :name="tab">
			<template #cell(requested_date)="{ item }">
				<span class="whitespace-nowrap">
					{{ useFormatTime(item.requested_date) }}
				</span>
			</template>

			<template #cell(overtime_type_name)="{ item }">
				<span class="whitespace-nowrap">
					{{ item.overtime_type_name }}
				</span>
			</template>

			<template #cell(requested_start_at)="{ item }">
				<span class="whitespace-nowrap">
					{{ useFormatTime(item.requested_start_at, { formatTime: "hm" }) }}
				</span>
			</template>

			<template #cell(requested_duration_minutes)="{ item }">
				<span class="whitespace-nowrap">
					{{ formatDuration(item.requested_duration_minutes) }}
				</span>
			</template>

            <template #cell(recorded_start_at)="{ item }">
				<span class="whitespace-nowrap">
					{{ useFormatTime(item.recorded_start_at, { formatTime: "hm" }) }}
				</span>
			</template>

			<template #cell(recorded_duration_minutes)="{ item }">
				<span class="whitespace-nowrap">
					{{ formatDuration(item.recorded_duration_minutes) }}
				</span>
			</template>

			<template #cell(lieu_requested_minutes)="{ item }">
				<span class="whitespace-nowrap">
					{{ formatDuration(item.lieu_requested_minutes) }}
				</span>
			</template>

			<template #cell(status)="{ item }">
				<Tag
					:theme="
						item.status === 'approved'
							? 3
							: item.status === 'pending'
								? 1
								: item.status === 'rejected'
									? 7
									: 99
					"
					size="xs"
					:label="item.status">
					<template #label>
						<span class="capitalize">
							{{ item.status }}
						</span>
					</template>
				</Tag>
			</template>

			<template #cell(reason)="{ item }">
				<span class="whitespace-nowrap">
					{{ item.reason ?? "—" }}
				</span>
			</template>

			<template #cell(actions)="{ item }">
				<div v-if="item.status === 'pending'">
					<Button
						@click="openEditModal(item)"
						size="xs"
						type="transparent"
						icon="fal fa-edit" />
				</div>
			</template>
		</Table>

		<Dialog
			:visible="isRequestOvertimeModalOpen"
			modal
			:draggable="false"
			:header="isEditMode ? ctrans('Edit overtime request') : ctrans('New overtime request')"
			:style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }"
			@update:visible="(isVisible: boolean) => !isVisible && closeModal()">
			<form class="space-y-4" @submit.prevent="submitOvertimeRequest">
				<div>
					<label for="overtime-type" class="block text-sm font-medium text-gray-700 mb-1">
						{{ ctrans("Overtime type") }} <span class="text-red-500">*</span>
					</label>
					<Select
						v-model="overtimeForm.overtime_type_id"
						inputId="overtime-type"
						:options="overtimeTypeSelectOptions"
						optionLabel="label"
						optionValue="value"
						:invalid="!!overtimeForm.errors.overtime_type_id"
						:placeholder="ctrans('Select overtime type')"
						fluid
						:class="fieldFocusClass" />
					<p v-if="overtimeForm.errors.overtime_type_id" class="mt-1 text-sm text-red-500">
						{{ overtimeForm.errors.overtime_type_id }}
					</p>
				</div>

				<div>
					<label for="overtime-date" class="block text-sm font-medium text-gray-700 mb-1">
						{{ ctrans("Date worked") }} <span class="text-red-500">*</span>
					</label>
					<DatePicker
						v-model="overtimeRequestedDateModel"
						inputId="overtime-date"
						dateFormat="d M yy"
						:manualInput="false"
						:invalid="!!overtimeForm.errors.requested_date"
						:placeholder="ctrans('Select a date')"
						showIcon
						iconDisplay="input"
						fluid
						:class="fieldFocusClass" />
					<p v-if="overtimeForm.errors.requested_date" class="mt-1 text-sm text-red-500">
						{{ overtimeForm.errors.requested_date }}
					</p>
				</div>

				<OvertimeTimeFields
					v-model:startHour="overtimeForm.start_hour"
					v-model:startMinute="overtimeForm.start_minute"
					v-model:durationHours="overtimeForm.duration_hours"
					v-model:durationMinutes="overtimeForm.duration_minutes"
					idPrefix="overtime-employee"
					:startError="getOvertimeError('requested_start_at')"
					:durationError="getOvertimeError('requested_duration_minutes') ?? getOvertimeError('duration_hours')" />

				<div>
					<label for="overtime-reason" class="block text-sm font-medium text-gray-700 mb-1">
						{{ ctrans("Reason") }}
					</label>
					<Textarea
						v-model="overtimeForm.reason"
						id="overtime-reason"
						rows="3"
						autoResize
						fluid
						:invalid="!!overtimeForm.errors.reason"
						:placeholder="ctrans('Why did you work overtime?')"
						class="focus:!border-[--app-accent]" />
					<p v-if="overtimeForm.errors.reason" class="mt-1 text-sm text-red-500">
						{{ overtimeForm.errors.reason }}
					</p>
				</div>

				<div class="flex justify-end gap-2 pt-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click.prevent="closeModal">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button
						type="tertiary"
						nativeType="submit"
						:label="isEditMode ? ctrans('Update Request') : ctrans('Submit Request')"
						:loading="overtimeForm.processing">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</form>
		</Dialog>
	</div>
</template>

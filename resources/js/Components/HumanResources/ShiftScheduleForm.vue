<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClock, faTimes, faCheck, faCopy } from "@fal"
import InputText from "primevue/inputtext"
import DatePicker from "primevue/datepicker"
import ToggleSwitch from "primevue/toggleswitch"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { fieldFocusClass, inputFocusClass } from "@/Composables/useHrFormControls"

library.add(faClock, faTimes, faCheck, faCopy)

interface BreakPayload {
	s: string | null
	e: string | null
	n?: string | null
	p?: boolean
}

interface ScheduleDay {
	day_of_week: number
	is_working_day: boolean
	start_time: string | null
	end_time: string | null
	breaks?: { start_time: string | null; end_time: string | null; break_name?: string | null; is_paid?: boolean }[]
}

interface DayRow {
	day: number
	label: string
	working: boolean
	start: Date | null
	end: Date | null
	breaks: BreakPayload[]
}

const props = defineProps<{
	schedule?: {
		id: number
		name: string
		days?: ScheduleDay[]
	} | null
}>()

const emit = defineEmits<{
	(e: "saved", schedule: unknown): void
	(e: "cancel"): void
}>()

const defaultStart = "08:00"
const defaultEnd = "17:00"
const minutesPerDay = 24 * 60

const weekdays = [
	{ day: 1, label: ctrans("Monday") },
	{ day: 2, label: ctrans("Tuesday") },
	{ day: 3, label: ctrans("Wednesday") },
	{ day: 4, label: ctrans("Thursday") },
	{ day: 5, label: ctrans("Friday") },
	{ day: 6, label: ctrans("Saturday") },
	{ day: 7, label: ctrans("Sunday") },
]

const twoDigits = (value: number) => String(value).padStart(2, "0")

const toTimeString = (value: Date | null) =>
	value ? `${twoDigits(value.getHours())}:${twoDigits(value.getMinutes())}` : null

const toTimeDate = (value: string | null | undefined) => {
	if (!value) return null
	const [hours, minutes] = value.split(":").map(Number)
	if (Number.isNaN(hours) || Number.isNaN(minutes)) return null
	const time = new Date()
	time.setHours(hours, minutes, 0, 0)
	return time
}

const toMinutes = (value: Date) => value.getHours() * 60 + value.getMinutes()

const buildRows = (): DayRow[] =>
	weekdays.map(({ day, label }) => {
		const saved = props.schedule?.days?.find((scheduleDay) => Number(scheduleDay.day_of_week) === day)
		const isSavedWorkingDay = !!saved?.is_working_day && !!saved.start_time && !!saved.end_time

		if (isSavedWorkingDay) {
			return {
				day,
				label,
				working: true,
				start: toTimeDate(saved.start_time),
				end: toTimeDate(saved.end_time),
				breaks: (saved.breaks ?? []).map((savedBreak) => ({
					s: savedBreak.start_time || null,
					e: savedBreak.end_time || null,
					n: savedBreak.break_name ?? null,
					p: !!savedBreak.is_paid,
				})),
			}
		}

		return {
			day,
			label,
			working: !props.schedule && day <= 5,
			start: toTimeDate(defaultStart),
			end: toTimeDate(defaultEnd),
			breaks: [],
		}
	})

const name = ref(props.schedule?.name ?? "")
const rows = reactive<DayRow[]>(buildRows())
const isSubmitting = ref(false)
const hasTriedToSubmit = ref(false)
const serverNameError = ref<string | null>(null)
const serverFormError = ref<string | null>(null)
const serverRowErrors = reactive<Record<number, string>>({})

const isEditing = computed(() => !!props.schedule)

const nameError = computed(() => {
	if (serverNameError.value) return serverNameError.value
	if (hasTriedToSubmit.value && !name.value.trim()) return ctrans("Give the shift a name.")
	return null
})

const rowMinutes = (row: DayRow) => {
	if (!row.working || !row.start || !row.end) return 0
	const difference = toMinutes(row.end) - toMinutes(row.start)
	return difference > 0 ? difference : difference + minutesPerDay
}

const isOvernight = (row: DayRow) =>
	row.working && !!row.start && !!row.end && toMinutes(row.end) < toMinutes(row.start)

const rowError = (row: DayRow) => {
	if (!row.working) return null
	if (!row.start || !row.end) return ctrans("Set both a start and an end time, or switch the day off.")
	if (toMinutes(row.start) === toMinutes(row.end)) return ctrans("The end time must be different from the start time.")
	return serverRowErrors[row.day] ?? null
}

const formatDuration = (minutes: number) => {
	const hours = Math.floor(minutes / 60)
	const remainder = minutes % 60
	return remainder ? `${hours}h ${remainder}m` : `${hours}h`
}

const workingDaysCount = computed(() => rows.filter((row) => row.working).length)
const weeklyMinutes = computed(() => rows.reduce((total, row) => total + (rowError(row) ? 0 : rowMinutes(row)), 0))
const hasRowErrors = computed(() => rows.some((row) => !!rowError(row)))

const monday = computed(() => rows[0])
const canCopyMonday = computed(
	() => monday.value.working && !!monday.value.start && !!monday.value.end && rows.some((row) => row.day !== 1 && row.working)
)

const copyMondayToWorkingDays = () => {
	rows.forEach((row) => {
		if (row.day === 1 || !row.working) return
		row.start = monday.value.start ? new Date(monday.value.start) : null
		row.end = monday.value.end ? new Date(monday.value.end) : null
	})
}

watch(name, () => {
	serverNameError.value = null
})

watch(
	rows,
	() => {
		Object.keys(serverRowErrors).forEach((day) => delete serverRowErrors[Number(day)])
		serverFormError.value = null
	},
	{ deep: true }
)

const buildPayload = () => {
	const data: Record<number, { s: string | null; e: string | null; b: BreakPayload[] }> = {}

	rows.forEach((row) => {
		data[row.day] = row.working
			? { s: toTimeString(row.start), e: toTimeString(row.end), b: row.breaks }
			: { s: null, e: null, b: [] }
	})

	return {
		name: name.value.trim(),
		...(isEditing.value ? {} : { type: "shift" }),
		working_hours: { data },
	}
}

const firstMessage = (messages: string[] | string) => (Array.isArray(messages) ? messages[0] : messages)

const applyServerErrors = (errors: Record<string, string[] | string>) => {
	Object.entries(errors).forEach(([field, messages]) => {
		const dayMatch = field.match(/^working_hours\.data\.(\d)/)

		if (field === "name") {
			serverNameError.value = firstMessage(messages)
		} else if (dayMatch) {
			serverRowErrors[Number(dayMatch[1])] = firstMessage(messages)
		} else {
			serverFormError.value = firstMessage(messages)
		}
	})
}

const submit = async () => {
	hasTriedToSubmit.value = true

	if (!name.value.trim() || hasRowErrors.value) return

	isSubmitting.value = true

	try {
		const organisation = route().params.organisation
		const response = props.schedule
			? await axios.patch(route("grp.org.hr.shift_schedules.update", [organisation, props.schedule.id]), buildPayload())
			: await axios.post(route("grp.org.hr.shift_schedules.store", organisation), buildPayload())

		emit("saved", response.data)
	} catch (error: any) {
		if (error.response?.status === 422 && error.response.data?.errors) {
			applyServerErrors(error.response.data.errors)
		} else {
			notify({
				title: ctrans("Something went wrong"),
				text: error.response?.data?.message || ctrans("The shift schedule could not be saved."),
				type: "error",
			})
		}
	} finally {
		isSubmitting.value = false
	}
}

const toggleAccentClass = "[&.p-toggleswitch-checked_.p-toggleswitch-slider]:!bg-[--app-accent]"
const rowGridClass = "grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 gap-y-2 sm:grid-cols-[2.75rem_6.5rem_minmax(0,1fr)_6.5rem]"
</script>

<template>
	<form class="space-y-6" @submit.prevent="submit">
		<div>
			<label for="shift-schedule-name" class="block text-sm font-medium text-gray-700 mb-1">
				{{ ctrans("Shift name") }} <span class="text-red-500">*</span>
			</label>
			<InputText
				v-model="name"
				id="shift-schedule-name"
				fluid
				maxlength="255"
				:invalid="!!nameError"
				:placeholder="ctrans('e.g. Morning shift, Evening shift, Night shift')"
				:class="inputFocusClass" />
			<p v-if="nameError" class="text-sm text-red-500 mt-1">{{ nameError }}</p>
		</div>

		<div>
			<div class="mb-2 flex flex-wrap items-center justify-between gap-2">
				<div>
					<h3 class="text-sm font-medium text-gray-700">{{ ctrans("Working hours") }}</h3>
					<p class="text-xs text-gray-500">{{ ctrans("Switch a day off to mark it as a day off. An end time earlier than the start time ends the next day.") }}</p>
				</div>
				<Button
					type="tertiary"
					size="xs"
					:label="ctrans('Copy Monday to all working days')"
					:disabled="!canCopyMonday"
					@click="copyMondayToWorkingDays">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-copy" class="text-[--app-accent]" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>

			<div class="overflow-hidden rounded-lg border border-gray-200">
				<div :class="rowGridClass" class="hidden border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs font-medium uppercase tracking-wide text-gray-500 sm:grid">
					<span class="col-span-2">{{ ctrans("Day") }}</span>
					<div class="grid grid-cols-2 gap-2">
						<span>{{ ctrans("Start") }}</span>
						<span>{{ ctrans("End") }}</span>
					</div>
					<span class="text-right">{{ ctrans("Hours") }}</span>
				</div>

				<div
					v-for="row in rows"
					:key="row.day"
					:class="[rowGridClass, row.working ? 'bg-white' : 'bg-gray-50/60']"
					class="border-b border-gray-100 px-4 py-2.5 last:border-b-0">
					<ToggleSwitch
						v-model="row.working"
						:inputId="`shift-schedule-day-${row.day}`"
						:class="toggleAccentClass"
						class="order-1" />

					<label
						:for="`shift-schedule-day-${row.day}`"
						class="order-2 cursor-pointer text-sm font-medium"
						:class="row.working ? 'text-gray-900' : 'text-gray-400'">
						{{ row.label }}
					</label>

					<div class="order-4 col-span-3 grid grid-cols-2 gap-2 sm:order-3 sm:col-span-1">
						<DatePicker
							v-model="row.start"
							timeOnly
							hourFormat="24"
							:stepMinute="5"
							:disabled="!row.working"
							:invalid="!!rowError(row)"
							:placeholder="defaultStart"
							:ariaLabel="ctrans(':day start time', { day: row.label })"
							showIcon
							iconDisplay="input"
							fluid
							:class="fieldFocusClass">
							<template #inputicon="{ clickCallback }">
								<FontAwesomeIcon icon="fal fa-clock" class="text-gray-400 cursor-pointer" fixed-width aria-hidden="true" @click="clickCallback" />
							</template>
						</DatePicker>
						<DatePicker
							v-model="row.end"
							timeOnly
							hourFormat="24"
							:stepMinute="5"
							:disabled="!row.working"
							:invalid="!!rowError(row)"
							:placeholder="defaultEnd"
							:ariaLabel="ctrans(':day end time', { day: row.label })"
							showIcon
							iconDisplay="input"
							fluid
							:class="fieldFocusClass">
							<template #inputicon="{ clickCallback }">
								<FontAwesomeIcon icon="fal fa-clock" class="text-gray-400 cursor-pointer" fixed-width aria-hidden="true" @click="clickCallback" />
							</template>
						</DatePicker>
					</div>

					<div class="order-3 text-right text-sm tabular-nums sm:order-4">
						<span v-if="!row.working" class="text-gray-400">{{ ctrans("Day off") }}</span>
						<span v-else-if="rowError(row)" class="text-gray-400">–</span>
						<template v-else>
							<span class="font-medium text-gray-900">{{ formatDuration(rowMinutes(row)) }}</span>
							<span v-if="isOvernight(row)" class="block text-xs text-amber-600">{{ ctrans("ends next day") }}</span>
						</template>
					</div>

					<p v-if="rowError(row)" class="order-5 col-span-full text-xs text-red-500 sm:col-span-2 sm:col-start-3">
						{{ rowError(row) }}
					</p>
					<p v-else-if="row.working && row.breaks.length" class="order-5 col-span-full text-xs text-gray-500 sm:col-span-2 sm:col-start-3">
						{{ ctrans(":count break(s) on this day are kept as they are.", { count: String(row.breaks.length) }) }}
					</p>
				</div>

				<div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-2.5 text-sm">
					<span class="text-gray-600">
						{{ ctrans("Weekly total") }}
						<span class="text-gray-400">· {{ ctrans(":count working day(s)", { count: String(workingDaysCount) }) }}</span>
					</span>
					<span class="font-semibold tabular-nums text-gray-900">{{ formatDuration(weeklyMinutes) }}</span>
				</div>
			</div>

			<p v-if="serverFormError" class="text-sm text-red-500 mt-2">{{ serverFormError }}</p>
			<slot name="hint" />
		</div>

		<div class="flex justify-end gap-2 border-t border-gray-200 pt-4">
			<Button type="tertiary" :label="ctrans('Cancel')" :disabled="isSubmitting" @click="emit('cancel')">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" nativeType="submit" :label="isEditing ? ctrans('Save') : ctrans('Create')" :loading="isSubmitting">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</div>
	</form>
</template>

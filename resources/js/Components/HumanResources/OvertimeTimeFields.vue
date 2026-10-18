<script setup lang="ts">
import { computed } from "vue"
import DatePicker from "primevue/datepicker"
import InputNumber from "primevue/inputnumber"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClock } from "@fal"
import { ctrans } from "@/Composables/useTrans"

library.add(faClock)

withDefaults(
	defineProps<{
		idPrefix: string
		startLabel?: string
		durationLabel?: string
		startError?: string
		durationError?: string
		durationRequired?: boolean
	}>(),
	{ durationRequired: true }
)

const startHour = defineModel<string>("startHour", { default: "00" })
const startMinute = defineModel<string>("startMinute", { default: "00" })
const durationHours = defineModel<string>("durationHours", { default: "0" })
const durationMinutes = defineModel<string>("durationMinutes", { default: "0" })

const twoDigits = (value: number): string => String(value).padStart(2, "0")

const startTime = computed<Date | null>({
	get: () => {
		const time = new Date()
		time.setHours(Number(startHour.value) || 0, Number(startMinute.value) || 0, 0, 0)
		return time
	},
	set: (value) => {
		startHour.value = twoDigits(value ? value.getHours() : 0)
		startMinute.value = twoDigits(value ? value.getMinutes() : 0)
	},
})

const hours = computed<number | null>({
	get: () => Number(durationHours.value) || 0,
	set: (value) => {
		durationHours.value = String(value ?? 0)
	},
})

const minutes = computed<number | null>({
	get: () => Number(durationMinutes.value) || 0,
	set: (value) => {
		durationMinutes.value = String(value ?? 0)
	},
})

const endsAt = computed<{ time: string; nextDay: boolean } | null>(() => {
	const duration = (hours.value ?? 0) * 60 + (minutes.value ?? 0)

	if (duration <= 0) {
		return null
	}

	const end = (Number(startHour.value) || 0) * 60 + (Number(startMinute.value) || 0) + duration

	return {
		time: `${twoDigits(Math.floor(end / 60) % 24)}:${twoDigits(end % 60)}`,
		nextDay: end >= 1440,
	}
})

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const durationPt = { pcInputText: { root: { class: "!w-full !min-w-0" } } }
</script>

<template>
	<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
		<div>
			<label :for="`${idPrefix}-start`" class="mb-1 block text-sm font-medium text-gray-700">
				{{ startLabel ?? ctrans("Start time") }}
			</label>
			<DatePicker
				v-model="startTime"
				:inputId="`${idPrefix}-start`"
				timeOnly
				hourFormat="24"
				:stepMinute="5"
				:invalid="!!startError"
				showIcon
				iconDisplay="input"
				fluid
				:class="fieldFocusClass">
				<template #inputicon="{ clickCallback }">
					<FontAwesomeIcon :icon="faClock" class="cursor-pointer text-gray-400" fixed-width aria-hidden="true" @click="clickCallback" />
				</template>
			</DatePicker>
			<p v-if="startError" class="mt-1 text-sm text-red-500">{{ startError }}</p>
		</div>

		<div>
			<span class="mb-1 block text-sm font-medium text-gray-700">
				{{ durationLabel ?? ctrans("Duration") }} <span v-if="durationRequired" class="text-red-500">*</span>
			</span>
			<div class="flex items-center gap-2">
				<InputNumber
					v-model="hours"
					:min="0"
					:max="24"
					suffix=" h"
					showButtons
					:invalid="!!durationError"
					:aria-label="ctrans('Hours')"
					class="min-w-0 flex-1"
					:class="fieldFocusClass"
					:pt="durationPt" />
				<InputNumber
					v-model="minutes"
					:min="0"
					:max="59"
					:step="5"
					suffix=" min"
					showButtons
					:invalid="!!durationError"
					:aria-label="ctrans('Minutes')"
					class="min-w-0 flex-1"
					:class="fieldFocusClass"
					:pt="durationPt" />
			</div>
			<p v-if="durationError" class="mt-1 text-sm text-red-500">{{ durationError }}</p>
			<p v-else-if="endsAt" class="mt-1 text-xs tabular-nums text-gray-500">
				{{ ctrans("Ends at :time", { time: endsAt.time }) }}
				<span v-if="endsAt.nextDay">({{ ctrans("next day") }})</span>
			</p>
		</div>
	</div>
</template>

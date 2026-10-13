<script setup lang="ts">
import { computed } from "vue"
import InputText from "primevue/inputtext"
import IconField from "primevue/iconfield"
import InputIcon from "primevue/inputicon"
import DatePicker from "primevue/datepicker"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSearch, faTimes } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLayoutStore } from "@/Stores/layout"

library.add(faSearch, faTimes)

const props = withDefaults(
	defineProps<{
		total: number
		countLabel: string
		loading?: boolean
		statusOptions: Array<{ label: string; value: string; icon?: any; tone?: "gray" | "emerald" | "amber" | "red" }>
		allStatusValue?: string
		searchPlaceholder?: string
		extraFilterActive?: boolean
	}>(),
	{ loading: false, allStatusValue: "all", extraFilterActive: false }
)

const emit = defineEmits<{
	(e: "clear"): void
}>()

const search = defineModel<string>("search", { default: "" })
const status = defineModel<string>("status", { default: "all" })
const from = defineModel<string>("from", { default: "" })
const to = defineModel<string>("to", { default: "" })

const twoDigits = (value: number): string => String(value).padStart(2, "0")

const isoToDate = (iso: string): Date | null => {
	const [year, month, day] = iso.slice(0, 10).split("-").map(Number)
	return year && month && day ? new Date(year, month - 1, day) : null
}

const dateToIso = (date: Date | null): string =>
	date ? `${date.getFullYear()}-${twoDigits(date.getMonth() + 1)}-${twoDigits(date.getDate())}` : ""

const fromDate = computed<Date | null>({
	get: () => isoToDate(from.value ?? ""),
	set: (value) => {
		from.value = dateToIso(value)
	},
})

const toDate = computed<Date | null>({
	get: () => isoToDate(to.value ?? ""),
	set: (value) => {
		to.value = dateToIso(value)
	},
})

const hasActiveFilters = computed(
	() => (search.value ?? "").trim() !== "" || status.value !== props.allStatusValue || !!from.value || !!to.value || props.extraFilterActive
)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const datePickerPt = { pcInputText: { root: { class: "!w-32 !py-1.5 !text-sm" } } }

const layout = useLayoutStore()
const stickyTopClass = computed(() => (layout.hasTopBanner ? "top-[61px] lg:top-[68px]" : "top-[33px] lg:top-10"))
</script>

<template>
	<div class="sticky z-10 flex flex-col gap-3 border-b border-gray-200 bg-white px-4 py-3 shadow-sm lg:flex-row lg:items-center lg:justify-between" :class="stickyTopClass">
		<div class="flex items-center gap-2 text-sm text-gray-700" aria-live="polite">
			<span class="font-semibold tabular-nums text-gray-900">{{ total }}</span>
			<span>{{ countLabel }}</span>
			<LoadingIcon v-if="loading" class="text-[--app-accent]" />
		</div>

		<div class="flex flex-wrap items-center gap-2">
			<IconField class="w-full sm:w-64">
				<InputIcon>
					<FontAwesomeIcon :icon="faSearch" class="text-gray-400" fixed-width aria-hidden="true" />
				</InputIcon>
				<InputText
					v-model="search"
					size="small"
					fluid
					:placeholder="searchPlaceholder ?? ctrans('Search')"
					:aria-label="searchPlaceholder ?? ctrans('Search')"
					class="focus:!border-[--app-accent]" />
			</IconField>

			<SegmentedToggle v-model="status" :options="statusOptions" :ariaLabel="ctrans('Status')" />

			<slot name="extra" />

			<div class="flex items-center gap-1.5">
				<DatePicker
					v-model="fromDate"
					:maxDate="toDate ?? undefined"
					dateFormat="d M yy"
					:manualInput="false"
					:placeholder="ctrans('From')"
					:aria-label="ctrans('From')"
					showIcon
					iconDisplay="input"
					showClear
					:class="fieldFocusClass"
					:pt="datePickerPt" />
				<span class="text-gray-400">–</span>
				<DatePicker
					v-model="toDate"
					:minDate="fromDate ?? undefined"
					dateFormat="d M yy"
					:manualInput="false"
					:placeholder="ctrans('To')"
					:aria-label="ctrans('To')"
					showIcon
					iconDisplay="input"
					showClear
					:class="fieldFocusClass"
					:pt="datePickerPt" />
			</div>

			<Button v-if="hasActiveFilters" type="tertiary" size="sm" :label="ctrans('Clear filters')" @click="emit('clear')">
				<template #icon>
					<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</div>
	</div>
</template>

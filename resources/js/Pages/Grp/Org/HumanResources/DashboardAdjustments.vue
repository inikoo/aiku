<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { computed, reactive, ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { PageHeadingTypes } from "@/types/PageHeading"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLayoutStore } from "@/Stores/layout"
import Button from "@/Components/Elements/Buttons/Button.vue"
import EmployeeAvatar from "@/Components/HumanResources/EmployeeAvatar.vue"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronLeft, faChevronRight, faFilter, faTimes } from "@fal"

library.add(faChevronLeft, faChevronRight, faFilter, faTimes)

const props = defineProps<{
	pageHead: PageHeadingTypes
	title: string
	filters: {
		year: number
		month: number
		employee_id?: number
		status?: string
	}
	calendarData: {
		id: number
		name: string
		avatar: string | null
		adjustments: {
			id: number
			date: string
			original_start_at: string | null
			original_end_at: string | null
			requested_start_at: string | null
			requested_end_at: string | null
			reason: string
			status: string
		}[]
	}[]
	daysInMonth: number
	monthName: string
	employeeOptions: { value: number; label: string }[]
	statusOptions: { value: string; label: string }[]
}>()

const selectedFilters = reactive({
	year: props.filters.year,
	month: props.filters.month,
	employee_id: props.filters.employee_id ?? null,
	status: props.filters.status ?? null,
})

const layout = useLayoutStore()
const stickyTopClass = computed(() => (layout.hasTopBanner ? "top-[61px] lg:top-[68px]" : "top-[33px] lg:top-10"))
const filtersOpen = ref(false)
const activeFilterCount = computed(() => [selectedFilters.employee_id, selectedFilters.status].filter((value) => value !== null && value !== undefined && value !== "").length)
const gridHeaderScroller = ref<HTMLElement | null>(null)
const syncGridHeaderScroll = (event: Event) => {
	if (gridHeaderScroller.value) {
		gridHeaderScroller.value.scrollLeft = (event.target as HTMLElement).scrollLeft
	}
}

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const showModal = ref(false)
const selectedAdjustment = ref<any>(null)

const openModal = (adjustment: any) => {
	selectedAdjustment.value = adjustment
	showModal.value = true
}

const closeModal = () => {
	showModal.value = false
	selectedAdjustment.value = null
}

const monthOptions = computed(() => {
	return Array.from({ length: 12 }, (_, i) => {
		const date = new Date(2000, i, 1)
		return {
			value: i + 1,
			label: date.toLocaleString("default", { month: "long" }),
		}
	})
})

const yearOptions = computed(() => {
	const currentYear = new Date().getFullYear()
	const years = new Set(Array.from({ length: 5 }, (_, i) => currentYear - 2 + i))
	years.add(selectedFilters.year)

	return [...years].sort((a, b) => a - b).map((year) => ({ value: year, label: String(year) }))
})

const periodSelectPt = { label: { class: "!text-base !font-semibold !text-gray-800" } }

const updateFilter = () => {
	router.get(
		route("grp.org.hr.adjustments.dashboard", route().params),
		{
			year: selectedFilters.year,
			month: selectedFilters.month,
			employee_id: selectedFilters.employee_id,
			status: selectedFilters.status,
		},
		{
			preserveState: true,
			preserveScroll: true,
		}
	)
}

const shiftMonth = (delta: number) => {
	const target = new Date(selectedFilters.year, selectedFilters.month - 1 + delta, 1)
	selectedFilters.year = target.getFullYear()
	selectedFilters.month = target.getMonth() + 1
	updateFilter()
}

const prevMonth = () => shiftMonth(-1)

const nextMonth = () => shiftMonth(1)

const days = computed(() => {
	return Array.from({ length: props.daysInMonth }, (_, i) => i + 1)
})

const gridWidthStyle = computed(() => ({ minWidth: `${12 + days.value.length * 2.5}rem` }))

const getDayName = (day: number) => {
	const date = new Date(props.filters.year, props.filters.month - 1, day)
	return date.toLocaleDateString("en-US", { weekday: "short" }).slice(0, 2)
}

const isWeekend = (day: number) => {
	const date = new Date(props.filters.year, props.filters.month - 1, day)
	const dayOfWeek = date.getDay()
	return dayOfWeek === 0 || dayOfWeek === 6
}

const getAdjustmentsForDay = (adjustments: any[], day: number) => {
	const dateStr = `${props.filters.year}-${String(props.filters.month).padStart(2, "0")}-${String(day).padStart(2, "0")}`

	return adjustments.filter((adjustment) => adjustment.date === dateStr)
}

const getStatusColor = (status: string): string => {
	switch (status) {
		case "approved":
			return "#22C55E"
		case "pending":
			return "#EAB308"
		case "rejected":
			return "#EF4444"
		default:
			return "#4F46E5"
	}
}
</script>

<template>
	<Head :title="capitalize(title)" />

	<PageHeading :data="pageHead">
		<template #before-title> </template>
	</PageHeading>

	<div class="mt-5 bg-white shadow-sm rounded-lg p-4">
		<div class="sticky z-10 -mx-4 -mt-4 rounded-t-lg bg-white px-4 pt-4" :class="stickyTopClass">
		<div class="flex flex-col sm:flex-row max-lg:flex-wrap gap-3 lg:gap-4 items-center justify-between mb-3 lg:mb-6">
			<div class="flex max-w-full gap-2 items-center">
				<Button type="tertiary" size="sm" :tooltip="ctrans('Previous month')" @click="prevMonth">
					<template #icon>
						<FontAwesomeIcon :icon="faChevronLeft" class="text-gray-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<h2 class="sr-only" aria-live="polite">{{ monthName }} {{ filters.year }}</h2>
				<Select
					v-model="selectedFilters.month"
					:options="monthOptions"
					optionLabel="label"
					optionValue="value"
					:aria-label="ctrans('Month')"
					size="small"
					class="w-40 min-w-0 sm:shrink-0"
					:class="fieldFocusClass"
					:pt="periodSelectPt"
					@update:modelValue="updateFilter" />
				<Select
					v-model="selectedFilters.year"
					:options="yearOptions"
					optionLabel="label"
					optionValue="value"
					:aria-label="ctrans('Year')"
					size="small"
					class="w-28 shrink-0"
					:class="fieldFocusClass"
					:pt="periodSelectPt"
					@update:modelValue="updateFilter" />
				<Button type="tertiary" size="sm" :tooltip="ctrans('Next month')" @click="nextMonth">
					<template #icon>
						<FontAwesomeIcon :icon="faChevronRight" class="text-gray-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>

			<div class="relative lg:hidden">
				<Button type="tertiary" size="sm" :label="ctrans('Filters')" :aria-expanded="filtersOpen" @click="filtersOpen = !filtersOpen">
					<template #icon>
						<FontAwesomeIcon :icon="faFilter" class="text-gray-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<span
					v-if="activeFilterCount"
					class="pointer-events-none absolute -right-1.5 -top-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[--app-accent] px-1 text-[10px] font-semibold leading-none tabular-nums text-[--app-accent-text]">
					{{ activeFilterCount }}
				</span>
			</div>

			<div class="gap-2 items-center flex-wrap max-lg:w-full max-lg:justify-center" :class="filtersOpen ? 'flex' : 'hidden lg:flex'">
				<Select
					v-model="selectedFilters.employee_id"
					:options="employeeOptions"
					optionLabel="label"
					optionValue="value"
					:placeholder="ctrans('All Employees')"
					size="small"
					filter
					showClear
					class="w-52"
					:class="fieldFocusClass"
					@update:modelValue="updateFilter" />

				<Select
					v-model="selectedFilters.status"
					:options="statusOptions"
					optionLabel="label"
					optionValue="value"
					:placeholder="ctrans('All Statuses')"
					size="small"
					showClear
					class="w-40"
					:class="fieldFocusClass"
					@update:modelValue="updateFilter" />
			</div>
		</div>

		<div ref="gridHeaderScroller" class="overflow-hidden">
			<div :style="gridWidthStyle">
				<table class="w-full table-fixed border-collapse">
					<colgroup>
						<col class="w-32 sm:w-48" />
						<col v-for="day in days" :key="day" />
					</colgroup>
					<thead>
						<tr>
							<th
								class="p-2 border-b border-r border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 z-10">
								{{ ctrans("Employee") }}
							</th>
							<th
								v-for="day in days"
								:key="day"
								class="p-1 border-b border-gray-200 bg-gray-50 text-center"
								:class="{ 'bg-gray-100': isWeekend(day) }">
								<div class="text-xs font-semibold text-gray-700">{{ day }}</div>
								<div class="text-[10px] text-gray-500">{{ getDayName(day) }}</div>
							</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
		</div>

		<div class="relative z-0 overflow-x-auto" @scroll.passive="syncGridHeaderScroll">
			<div :style="gridWidthStyle">
				<table class="w-full table-fixed border-collapse">
					<colgroup>
						<col class="w-32 sm:w-48" />
						<col v-for="day in days" :key="day" />
					</colgroup>
					<tbody class="bg-white divide-y divide-gray-200">
						<tr
							v-for="employee in calendarData"
							:key="employee.id"
							class="hover:bg-gray-50">
							<td
								class="p-2 border-r border-gray-200 truncate text-sm font-medium text-gray-900 sticky left-0 bg-white z-10"
								:title="employee.name">
								<div class="flex min-w-0 items-center gap-2">
									<EmployeeAvatar :name="employee.name" :image="employee.avatar" size="xs" class="shrink-0" />
									<span class="truncate">{{ employee.name }}</span>
								</div>
							</td>
							<td
								v-for="day in days"
								:key="day"
								class="p-1 border-r border-gray-100 h-12 relative align-top"
								:class="{ 'bg-gray-50': isWeekend(day) }">
								<div class="w-full h-full flex flex-col gap-0.5 justify-center">
									<template
										v-for="adjustment in getAdjustmentsForDay(
											employee.adjustments,
											day
										)"
										:key="adjustment.id">
										<div
											class="flex-1 min-h-[4px] rounded w-full cursor-pointer group relative flex items-center justify-center text-[10px] text-white font-medium hover:opacity-80 transition-opacity"
											:style="{
												backgroundColor: getStatusColor(adjustment.status),
											}"
											:title="`${adjustment.status}`"
											@click="openModal(adjustment)">
											A
										</div>
									</template>
								</div>
							</td>
						</tr>
						<tr v-if="calendarData.length === 0">
							<td :colspan="daysInMonth + 1" class="p-8 text-center text-gray-500">
								{{ ctrans("No employees found.") }}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<Modal :isOpen="showModal" @onClose="closeModal">
		<div class="p-6">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-lg font-medium text-gray-900">
					{{ ctrans("Adjustment Details") }}
				</h3>
				<button @click="closeModal" class="text-gray-400 hover:text-gray-500">
					<span class="sr-only">Close</span>
					<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>

			<div v-if="selectedAdjustment" class="space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Date")
						}}</label>
						<div class="mt-1 text-sm text-gray-900">{{ selectedAdjustment.date }}</div>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Status")
						}}</label>
						<div class="mt-1">
							<span
								class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
								:class="{
									'bg-green-100 text-green-800':
										selectedAdjustment.status === 'approved',
									'bg-yellow-100 text-yellow-800':
										selectedAdjustment.status === 'pending',
									'bg-red-100 text-red-800':
										selectedAdjustment.status === 'rejected',
								}">
								{{ capitalize(selectedAdjustment.status) }}
							</span>
						</div>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Original Start")
						}}</label>
						<div class="mt-1 text-sm text-gray-900">
							{{ selectedAdjustment.original_start_at ?? "—" }}
						</div>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Original End")
						}}</label>
						<div class="mt-1 text-sm text-gray-900">
							{{ selectedAdjustment.original_end_at ?? "—" }}
						</div>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Requested Start")
						}}</label>
						<div class="mt-1 text-sm text-gray-900">
							{{ selectedAdjustment.requested_start_at ?? "—" }}
						</div>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-500">{{
							ctrans("Requested End")
						}}</label>
						<div class="mt-1 text-sm text-gray-900">
							{{ selectedAdjustment.requested_end_at ?? "—" }}
						</div>
					</div>
				</div>

				<div v-if="selectedAdjustment.reason">
					<label class="block text-sm font-medium text-gray-500">{{
						ctrans("Reason")
					}}</label>
					<div
						class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md border border-gray-100">
						{{ selectedAdjustment.reason }}
					</div>
				</div>
			</div>

			<div class="mt-6 flex justify-end">
				<Button type="tertiary" :label="ctrans('Close')" @click="closeModal">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</div>
	</Modal>
</template>

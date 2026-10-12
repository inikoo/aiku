<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import { useMediaQuery } from "@vueuse/core"
import DatePicker from "primevue/datepicker"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faUsers, faUserCheck, faClock, faStopwatch, faUserClock, faSignOut, faUmbrellaBeach, faChartBar, faInfoCircle, faCalendarAlt } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import EmployeeAvatar from "@/Components/HumanResources/EmployeeAvatar.vue"
import { PageHeadingTypes } from "@/types/PageHeading"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"

library.add(faUsers, faUserCheck, faClock, faStopwatch, faUserClock, faSignOut, faUmbrellaBeach, faChartBar, faInfoCircle, faCalendarAlt)

interface EmployeeAttendance {
	id: number
	name: string
	slug: string
	attendance_percentage: number
	present_days: number
	attendance_eligible_days: number
	late_clockins: number
	early_clockouts: number
	avatar?: string | null
}

interface EmployeeLeave {
	id: number
	name: string
	slug: string
	total_leave_days: number
	leave_breakdown: Record<string, number>
	avatar?: string | null
}

interface EmployeeBar {
	id: number
	name: string
	avatar: string | null
	width: string
	colour: string
	value: string
	detail?: string
	tooltip?: string
}

interface AttendanceWindow {
	start: string
	end: string | null
	today: string
	status: "past" | "to_date" | "future"
	working_days: number
	employees_measured: number
}

type PresetKey = "this_week" | "this_month" | "last_month" | "this_quarter"
type Tone = "neutral" | "good" | "warning" | "bad" | "series"

const props = defineProps<{
	pageHead: PageHeadingTypes
	title: string
	filters: {
		start_date: string
		end_date: string
	}
	analytics: {
		avg_attendance_percentage: number | null
		avg_total_working_hours: number | null
		avg_overtime_hours: number | null
		total_late_clockins: number
		total_early_clockouts: number
		total_leave_days: number
	} | null
	total_employees: number
	attendance_window: AttendanceWindow
	attendance_breakdown: EmployeeAttendance[]
	top_employees_by_leave: EmployeeLeave[]
}>()

const ATTENDANCE_GOOD_FROM = 90
const ATTENDANCE_WARNING_FROM = 75
const CHART_COLOURS = {
	good: "#16a34a",
	warning: "#f59e0b",
	bad: "#dc2626",
	series: "#64748b",
}

const fromIsoDate = (iso: string): Date => {
	const [year, month, day] = iso.split("-").map(Number)
	return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const presetRange = (key: PresetKey): { from: string; to: string } => {
	const now = new Date()
	const year = now.getFullYear()
	const month = now.getMonth()
	if (key === "this_week") {
		const daysSinceMonday = (now.getDay() + 6) % 7
		const monday = new Date(year, month, now.getDate() - daysSinceMonday)
		const sunday = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + 6)
		return { from: toLocalIsoDate(monday), to: toLocalIsoDate(sunday) }
	}
	if (key === "this_month") {
		return { from: toLocalIsoDate(new Date(year, month, 1)), to: toLocalIsoDate(new Date(year, month + 1, 0)) }
	}
	if (key === "last_month") {
		return { from: toLocalIsoDate(new Date(year, month - 1, 1)), to: toLocalIsoDate(new Date(year, month, 0)) }
	}
	const quarterStartMonth = Math.floor(month / 3) * 3
	return { from: toLocalIsoDate(new Date(year, quarterStartMonth, 1)), to: toLocalIsoDate(new Date(year, quarterStartMonth + 3, 0)) }
}

const presetOptions: Array<{ label: string; value: PresetKey }> = [
	{ label: ctrans("This Week"), value: "this_week" },
	{ label: ctrans("This Month"), value: "this_month" },
	{ label: ctrans("Last Month"), value: "last_month" },
	{ label: ctrans("This Quarter"), value: "this_quarter" },
]

const range = ref({ from: props.filters.start_date, to: props.filters.end_date })
const isLoading = ref(false)

watch(
	() => [props.filters.start_date, props.filters.end_date],
	([from, to]) => {
		range.value = { from, to }
	}
)

const reload = () => {
	router.get(
		route("grp.org.hr.analytics.index", route().params),
		{
			start_date: range.value.from,
			end_date: range.value.to,
		},
		{
			preserveState: true,
			preserveScroll: true,
			onStart: () => (isLoading.value = true),
			onFinish: () => (isLoading.value = false),
		}
	)
}

const activePreset = computed<PresetKey | undefined>({
	get: () =>
		presetOptions.find((option) => {
			const preset = presetRange(option.value)
			return preset.from === range.value.from && preset.to === range.value.to
		})?.value,
	set: (key) => {
		if (!key) return
		range.value = presetRange(key)
		reload()
	},
})

const dateModel = (key: "from" | "to") =>
	computed<Date | null>({
		get: () => (range.value[key] ? fromIsoDate(range.value[key]) : null),
		set: (value) => {
			if (!value) return
			range.value = { ...range.value, [key]: toLocalIsoDate(value) }
			reload()
		},
	})
const rangeFrom = dateModel("from")
const rangeTo = dateModel("to")

const isNarrowScreen = useMediaQuery("(max-width: 639px)")
const dateFormat = computed(() => (isNarrowScreen.value ? "dd/mm/yy" : "d M yy"))
const datePickerPt = computed(() => ({ pcInputText: { root: { class: isNarrowScreen.value ? "!w-28 !py-1 !text-xs" : "!w-32 !py-1 !text-xs" } } }))
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const periodFormatter = new Intl.DateTimeFormat(undefined, { day: "numeric", month: "short", year: "numeric" })
const periodLabel = computed(() =>
	ctrans(":from to :to", {
		from: periodFormatter.format(fromIsoDate(props.filters.start_date)),
		to: periodFormatter.format(fromIsoDate(props.filters.end_date)),
	})
)
const attendanceIsToDate = computed(() => props.attendance_window.status === "to_date")
const attendanceHasWorkingDays = computed(() => props.attendance_window.status !== "future" && props.attendance_window.working_days > 0)
const attendanceWorksUpToToday = computed(() => props.attendance_window.status !== "past")

const attendancePeriodLabel = computed(() => {
	const attendanceWindow = props.attendance_window
	if (attendanceWindow.status === "future" || !attendanceWindow.end) return ctrans("No working days yet in this period")
	const from = periodFormatter.format(fromIsoDate(attendanceWindow.start))
	const to = periodFormatter.format(fromIsoDate(attendanceWindow.end))
	if (attendanceWindow.status === "to_date") return ctrans(":from to today, :to", { from, to })
	return ctrans(":from to :to", { from, to })
})

const workingDaysLabel = computed(() =>
	attendanceIsToDate.value
		? ctrans(":count working days so far", { count: props.attendance_window.working_days })
		: ctrans(":count working days", { count: props.attendance_window.working_days })
)

const hasValue = (value: number | null | undefined): value is number => value !== null && value !== undefined

const formatMetric = (value: number | null | undefined, suffix = "", decimals = 2): string => {
	if (!hasValue(value)) return "--"
	return Number(value).toFixed(decimals) + suffix
}

const formatDays = (value: number | null | undefined): string => {
	if (!hasValue(value)) return "--"
	return Number.isInteger(Number(value)) ? String(value) : Number(value).toFixed(1)
}

const attendanceTone = (value: number | null | undefined): Tone => {
	if (!hasValue(value)) return "neutral"
	if (value >= ATTENDANCE_GOOD_FROM) return "good"
	if (value >= ATTENDANCE_WARNING_FROM) return "warning"
	return "bad"
}

const countTone = (value: number | null | undefined): Tone => (hasValue(value) && value > 0 ? "warning" : "neutral")

const toneClasses: Record<Tone, { icon: string; value: string }> = {
	neutral: { icon: "bg-gray-100 text-gray-500", value: "text-gray-900" },
	good: { icon: "bg-green-100 text-green-600", value: "text-green-600" },
	warning: { icon: "bg-amber-100 text-amber-600", value: "text-amber-600" },
	bad: { icon: "bg-red-100 text-red-600", value: "text-red-600" },
	series: { icon: "bg-slate-100 text-slate-500", value: "text-gray-900" },
}

const summaryTiles = computed(() => [
	{
		key: "employees",
		label: ctrans("Total Employees"),
		value: formatMetric(props.total_employees, "", 0),
		caption: ctrans("Currently working"),
		icon: faUsers,
		tone: "neutral" as Tone,
	},
	{
		key: "attendance",
		label: ctrans("Avg Attendance %"),
		value: formatMetric(props.analytics?.avg_attendance_percentage, "%"),
		caption: attendanceHasWorkingDays.value
			? `${ctrans("Average of :count employees", { count: props.attendance_window.employees_measured })} · ${workingDaysLabel.value}`
			: ctrans("No working days yet in this period"),
		icon: faUserCheck,
		tone: attendanceTone(props.analytics?.avg_attendance_percentage),
	},
	{
		key: "working_hours",
		label: ctrans("Avg Working Hours"),
		value: formatMetric(props.analytics?.avg_total_working_hours, "h"),
		caption: ctrans("Per employee in the period"),
		icon: faClock,
		tone: "neutral" as Tone,
	},
	{
		key: "overtime_hours",
		label: ctrans("Avg Overtime Hours"),
		value: formatMetric(props.analytics?.avg_overtime_hours, "h"),
		caption: ctrans("Per employee in the period"),
		icon: faStopwatch,
		tone: "neutral" as Tone,
	},
])

const eventTiles = computed(() => [
	{
		key: "late_clockins",
		label: ctrans("Total Late Clock-ins"),
		value: formatMetric(props.analytics?.total_late_clockins, "", 0),
		caption: ctrans("All employees in the period"),
		icon: faUserClock,
		tone: countTone(props.analytics?.total_late_clockins),
	},
	{
		key: "early_clockouts",
		label: ctrans("Total Early Clock-outs"),
		value: formatMetric(props.analytics?.total_early_clockouts, "", 0),
		caption: ctrans("All employees in the period"),
		icon: faSignOut,
		tone: countTone(props.analytics?.total_early_clockouts),
	},
	{
		key: "leave_days",
		label: ctrans("Total Leave Days"),
		value: formatDays(props.analytics?.total_leave_days),
		caption: ctrans("Approved leave in the period"),
		icon: faUmbrellaBeach,
		tone: "series" as Tone,
	},
])

const tileGroups = computed(() => [
	{ key: "summary", grid: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-4", tiles: summaryTiles.value },
	{ key: "events", grid: "grid-cols-1 sm:grid-cols-3", tiles: eventTiles.value },
])

const attendanceLegend = [
	{ colour: CHART_COLOURS.good, label: ctrans(":value% and above", { value: ATTENDANCE_GOOD_FROM }) },
	{ colour: CHART_COLOURS.warning, label: ctrans(":from% to :to%", { from: ATTENDANCE_WARNING_FROM, to: ATTENDANCE_GOOD_FROM }) },
	{ colour: CHART_COLOURS.bad, label: ctrans("Below :value%", { value: ATTENDANCE_WARNING_FROM }) },
]

const LEAVE_BAR_MINIMUM_SCALE_DAYS = 5
const BAR_MINIMUM_WIDTH_PERCENT = 1

const barWidth = (value: number, scaleMax: number): string => `${Math.min(100, Math.max(BAR_MINIMUM_WIDTH_PERCENT, (value / scaleMax) * 100))}%`

const attendanceBars = computed<EmployeeBar[]>(() => {
	if (!attendanceHasWorkingDays.value) return []
	return (props.attendance_breakdown || []).map((employee) => {
		const percentage = Number(employee.attendance_percentage) || 0
		const tone = attendanceTone(percentage)
		const eligibleDays = Number(employee.attendance_eligible_days) || 0
		const days = { present: formatDays(Number(employee.present_days) || 0), eligible: formatDays(eligibleDays) }
		if (eligibleDays <= 0) {
			return {
				id: employee.id,
				name: employee.name,
				avatar: employee.avatar ?? null,
				width: "0%",
				colour: CHART_COLOURS.series,
				value: "--",
				detail: ctrans("No days expected"),
				tooltip: ctrans("No working days were expected from this employee in the period (on leave or not employed yet)"),
			}
		}
		return {
			id: employee.id,
			name: employee.name,
			avatar: employee.avatar ?? null,
			width: barWidth(percentage, 100),
			colour: tone === "good" ? CHART_COLOURS.good : tone === "warning" ? CHART_COLOURS.warning : CHART_COLOURS.bad,
			value: `${formatDays(percentage)}%`,
			detail: ctrans(":present of :eligible days", days),
			tooltip: attendanceIsToDate.value
				? ctrans("Present :present of :eligible working days so far (leave days excluded)", days)
				: ctrans("Present :present of :eligible working days (leave days excluded)", days),
		}
	})
})

const leaveBars = computed<EmployeeBar[]>(() => {
	const employees = props.top_employees_by_leave || []
	const scaleMax = Math.max(LEAVE_BAR_MINIMUM_SCALE_DAYS, ...employees.map((employee) => Number(employee.total_leave_days) || 0))
	return employees.map((employee) => ({
		id: employee.id,
		name: employee.name,
		avatar: employee.avatar ?? null,
		width: barWidth(Number(employee.total_leave_days) || 0, scaleMax),
		colour: CHART_COLOURS.series,
		value: formatDays(employee.total_leave_days),
	}))
})

const employeeBarCharts = computed(() => [
	{
		key: "attendance",
		title: ctrans("Employee Attendance"),
		subtitle: attendanceHasWorkingDays.value
			? `${ctrans("Top :count employees by attendance", { count: 10 })} · ${attendancePeriodLabel.value} · ${workingDaysLabel.value}`
			: ctrans("Top :count employees by attendance", { count: 10 }),
		rowClass: "grid-cols-[minmax(0,32%)_minmax(0,1fr)_8.5rem] sm:grid-cols-[11rem_minmax(0,1fr)_10rem]",
		bars: attendanceBars.value,
		legend: attendanceLegend,
		emptyIcon: attendanceHasWorkingDays.value ? faChartBar : faCalendarAlt,
		emptyTitle: attendanceHasWorkingDays.value ? ctrans("No attendance data for this period") : ctrans("No working days yet in this period"),
		emptyHint: attendanceHasWorkingDays.value
			? ctrans("Try a different date range.")
			: ctrans("Attendance is measured up to today. Choose a period that has already started."),
	},
	{
		key: "leave",
		title: ctrans("Top Employees by Leave"),
		subtitle: ctrans("Approved leave days for the whole selected period, :period", { period: periodLabel.value }),
		rowClass: "grid-cols-[minmax(0,42%)_minmax(0,1fr)_3.5rem] sm:grid-cols-[11rem_minmax(0,1fr)_3.5rem]",
		bars: leaveBars.value,
		legend: [{ colour: CHART_COLOURS.series, label: ctrans("Leave Days") }],
		emptyIcon: faUmbrellaBeach,
		emptyTitle: ctrans("No leave data for this period"),
		emptyHint: ctrans("Nobody has approved leave in this date range."),
	},
])
</script>

<template>
	<Head :title="capitalize(title)" />

	<PageHeading :data="pageHead" />

	<div class="mt-5 space-y-4 px-4 pb-6">
		<div class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="flex flex-col items-start justify-between gap-3 lg:flex-row lg:items-center">
				<div class="flex items-center gap-3">
					<SegmentedToggle v-model="activePreset" :options="presetOptions" :ariaLabel="ctrans('Period')" />
					<span v-if="isLoading" class="flex items-center gap-1.5 text-sm text-gray-500" role="status">
						<LoadingIcon />
						<span class="hidden sm:inline">{{ ctrans("Loading") }}</span>
					</span>
				</div>

				<div class="flex items-center gap-2">
					<DatePicker v-model="rangeFrom" :maxDate="rangeTo ?? undefined" :dateFormat="dateFormat" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" :aria-label="ctrans('From')" />
					<span class="text-sm text-gray-500">{{ ctrans("to") }}</span>
					<DatePicker v-model="rangeTo" :minDate="rangeFrom ?? undefined" :dateFormat="dateFormat" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" :aria-label="ctrans('To')" />
				</div>
			</div>
			<p v-if="!attendanceWorksUpToToday" class="mt-3 text-sm text-gray-600">
				{{ ctrans("All figures cover :period.", { period: periodLabel }) }}
			</p>
			<div v-else class="mt-3 flex items-start gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
				<FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 flex-shrink-0 text-gray-500" fixed-width aria-hidden="true" />
				<div>
					<p class="font-medium text-gray-900">{{ ctrans("Attendance is measured up to today. Days that have not happened yet are not counted.") }}</p>
					<p v-if="attendanceHasWorkingDays">{{ ctrans("Attendance covers :period (:days).", { period: attendancePeriodLabel, days: workingDaysLabel }) }}</p>
					<p v-else>{{ ctrans("No working days yet in this period") }}</p>
					<p>{{ ctrans("Leave covers the whole selected period, :period.", { period: periodLabel }) }}</p>
				</div>
			</div>
		</div>

		<div class="space-y-4 transition-opacity duration-150" :class="{ 'pointer-events-none opacity-50': isLoading }" :aria-busy="isLoading">
			<div v-for="group in tileGroups" :key="group.key" class="grid gap-4" :class="group.grid">
				<div v-for="tile in group.tiles" :key="tile.key" class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-5">
					<div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-md" :class="toneClasses[tile.tone].icon">
						<FontAwesomeIcon :icon="tile.icon" class="text-lg" fixed-width aria-hidden="true" />
					</div>
					<div class="min-w-0">
						<div class="text-sm font-medium text-gray-500">{{ tile.label }}</div>
						<div class="text-2xl font-bold tabular-nums" :class="toneClasses[tile.tone].value">{{ tile.value }}</div>
						<div class="text-xs text-gray-400">{{ tile.caption }}</div>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<div v-for="chart in employeeBarCharts" :key="chart.key" class="rounded-lg border border-gray-200 bg-white p-5">
					<h3 class="text-base font-semibold text-gray-900">{{ chart.title }}</h3>
					<p class="mt-0.5 text-xs text-gray-500">{{ chart.subtitle }}</p>
					<template v-if="chart.bars.length">
						<ul class="mt-4 space-y-2.5">
							<li v-for="bar in chart.bars" :key="bar.id" class="grid items-center gap-3" :class="chart.rowClass" :title="bar.tooltip">
								<div class="flex min-w-0 items-center gap-2">
									<EmployeeAvatar :name="bar.name" :image="bar.avatar" size="xs" />
									<span class="truncate text-sm text-gray-700" :title="bar.name">{{ bar.name }}</span>
								</div>
								<div class="h-3 overflow-hidden rounded bg-gray-100" aria-hidden="true">
									<div class="h-full rounded" :style="{ width: bar.width, backgroundColor: bar.colour }" />
								</div>
								<span class="text-right text-sm tabular-nums">
									<span v-if="bar.detail" class="text-gray-500">{{ bar.detail }} · </span>
									<span class="font-medium text-gray-900">{{ bar.value }}</span>
								</span>
							</li>
						</ul>
						<ul class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
							<li v-for="item in chart.legend" :key="item.label" class="flex items-center gap-1.5">
								<span class="h-2.5 w-2.5 rounded-sm" :style="{ backgroundColor: item.colour }" />
								{{ item.label }}
							</li>
						</ul>
					</template>
					<div v-else class="mt-4 flex h-80 flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-gray-200 bg-gray-50 text-center">
						<FontAwesomeIcon :icon="chart.emptyIcon" class="text-2xl text-gray-300" aria-hidden="true" />
						<div class="text-sm font-medium text-gray-600">{{ chart.emptyTitle }}</div>
						<div class="text-xs text-gray-400">{{ chart.emptyHint }}</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Link, router } from "@inertiajs/vue3"
import Select from "primevue/select"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Pagination from "@/Components/Table/Pagination.vue"
import HrRequestCard from "@/Components/HumanResources/HrRequestCard.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCalendarAlt, faCalendarCheck, faCheck, faCheckCircle, faClock, faExternalLinkAlt, faPaperclip, faTimes, faTimesCircle, faUserFriends } from "@fal"

library.add(faCalendarAlt, faCalendarCheck, faCheck, faCheckCircle, faClock, faExternalLinkAlt, faPaperclip, faTimes, faTimesCircle, faUserFriends)

type LeaveStatus = "pending" | "approved" | "rejected"
type StatusFilter = "all" | LeaveStatus
type YearFilter = number | "all"

type RouteTarget = { name: string; parameters: Record<string, any> }

type LeaveApproval = {
	level: number
	status: string
	approver_name: string | null
	decided_at: string | null
}

type LeaveRow = {
	id: number
	type_label: string | null
	type_color_hex: string
	start_date: string
	end_date: string | null
	days: number
	is_half_day: boolean
	session: string | null
	reason: string | null
	status: LeaveStatus
	approval_total_steps: number
	approval_completed_steps: number
	approval_current_step: number
	approved_at: string | null
	rejection_reason: string | null
	cover_employee_name: string | null
	approver_name: string | null
	approvals: LeaveApproval[]
	attachments: { id: number; name: string; url: string }[]
}

type EmployeeLeavesData = {
	summary: { total: number; approved: number; pending: number; rejected: number; days_taken: number }
	by_type: { key: string; label: string; color_hex: string; requests: number; approved: number; approved_days: number }[]
	balance: {
		annual_allowance: number
		annual_used: number
		annual_remaining: number
		medical_used: number
		unpaid_used: number
		period_start: string | null
		period_end: string | null
	} | null
	leaves: any
	filters: { status: LeaveStatus | null; year: YearFilter }
	year_options: number[]
	status_options: Record<string, string>
	query_parameters: { status: string; year: string; per_page: string }
	leave_requests_route: RouteTarget
}

const props = defineProps<{
	data?: EmployeeLeavesData
	tab: string
}>()

const locale = useLocaleStore()

const statusFilter = ref<StatusFilter>(props.data?.filters.status ?? "all")
const yearFilter = ref<YearFilter>(props.data?.filters.year ?? new Date().getFullYear())
const isLoading = ref(false)

const rows = computed<LeaveRow[]>(() => props.data?.leaves?.data ?? [])
const paginationMeta = computed(() => props.data?.leaves?.meta ?? props.data?.leaves ?? {})

const visitOptions = {
	only: ["leaves"],
	preserveState: true,
	preserveScroll: true,
	replace: true,
	onStart: () => {
		isLoading.value = true
	},
	onFinish: () => {
		isLoading.value = false
	},
}

const reload = (extraQuery: Record<string, string | number> = {}) => {
	const parameters = props.data?.query_parameters
	if (!parameters) return

	const query: Record<string, string | number> = { tab: props.tab, [parameters.year]: yearFilter.value, ...extraQuery }
	if (statusFilter.value !== "all") query[parameters.status] = statusFilter.value

	router.get(window.location.pathname, query, visitOptions)
}

watch([statusFilter, yearFilter], () => {
	const applied = props.data?.filters
	if (applied && (applied.status ?? "all") === statusFilter.value && applied.year === yearFilter.value) return

	reload()
})

watch(
	() => props.data?.filters,
	(applied) => {
		if (!applied) return
		statusFilter.value = applied.status ?? "all"
		yearFilter.value = applied.year
	}
)

const visitPage = (url?: string | null) => {
	if (!url) return
	router.get(url, {}, visitOptions)
}

const changePerPage = (perPage: number) => {
	const parameter = props.data?.query_parameters.per_page
	if (parameter) reload({ [parameter]: perPage })
}

const toggleStatus = (status: StatusFilter) => {
	statusFilter.value = statusFilter.value === status ? "all" : status
}

const yearOptions = computed(() => [
	{ label: ctrans("All time"), value: "all" as YearFilter },
	...(props.data?.year_options ?? []).map((year) => ({ label: String(year), value: year as YearFilter })),
])

const statusOptions = computed(() => [
	{ label: ctrans("All"), value: "all" as StatusFilter },
	{ label: ctrans("Pending"), value: "pending" as StatusFilter, tone: "amber" as const },
	{ label: ctrans("Approved"), value: "approved" as StatusFilter, tone: "emerald" as const },
	{ label: ctrans("Rejected"), value: "rejected" as StatusFilter, tone: "red" as const },
])

const periodLabel = computed(() => (yearFilter.value === "all" ? ctrans("All time") : String(yearFilter.value)))

const formatDays = (days: number | string): string => locale.number(Number(days) || 0)

const daysText = (days: number | string): string => (Number(days) === 1 ? ctrans("1 day") : ctrans(":days days", { days: formatDays(days) }))

const tiles = computed(() => {
	const summary = props.data?.summary

	return [
		{ key: "all" as StatusFilter, label: ctrans("Total requests"), value: summary?.total ?? 0, icon: faCalendarAlt, valueClass: "text-gray-900", iconClass: "text-gray-400", isFilter: true },
		{ key: "approved" as StatusFilter, label: ctrans("Approved"), value: summary?.approved ?? 0, icon: faCheckCircle, valueClass: "text-green-700", iconClass: "text-green-600", isFilter: true },
		{ key: "pending" as StatusFilter, label: ctrans("Pending"), value: summary?.pending ?? 0, icon: faClock, valueClass: "text-amber-700", iconClass: "text-amber-500", isFilter: true },
		{ key: "rejected" as StatusFilter, label: ctrans("Rejected"), value: summary?.rejected ?? 0, icon: faTimesCircle, valueClass: "text-red-700", iconClass: "text-red-500", isFilter: true },
		{ key: "days" as const, label: ctrans("Days taken"), value: summary?.days_taken ?? 0, icon: faCalendarCheck, valueClass: "text-gray-900", iconClass: "text-gray-400", isFilter: false },
	]
})

const balanceCells = computed(() => {
	const balance = props.data?.balance
	if (!balance) return []

	return [
		{ label: ctrans("Annual allowance"), value: balance.annual_allowance },
		{ label: ctrans("Annual used"), value: balance.annual_used },
		{ label: ctrans("Annual left"), value: balance.annual_remaining },
		{ label: ctrans("Sick days"), value: balance.medical_used },
		{ label: ctrans("Unpaid days"), value: balance.unpaid_used },
	]
})

const largestApprovedDays = computed(() => Math.max(0, ...(props.data?.by_type ?? []).map((type) => type.approved_days)))

const typeBarWidth = (approvedDays: number): string => (largestApprovedDays.value > 0 ? `${Math.round((approvedDays / largestApprovedDays.value) * 100)}%` : "0%")

const typeTagStyle = (color: string) => ({
	backgroundColor: `color-mix(in srgb, ${color} 12%, white)`,
	borderColor: `color-mix(in srgb, ${color} 35%, white)`,
	color: `color-mix(in srgb, ${color} 75%, black)`,
})

const formatDate = (date: string | null) => useFormatTime(date ?? undefined, { localeCode: locale?.language?.code })

const formatDateTime = (date: string | null) => useFormatTime(date ?? undefined, { formatTime: "hm", localeCode: locale?.language?.code })

const statusLabel = (status: string): string => props.data?.status_options?.[status] ?? status

const approvalTotalSteps = (leave: LeaveRow): number => Math.max(0, Number(leave.approval_total_steps) || 0)

const approvalCompletedSteps = (leave: LeaveRow): number => {
	const total = approvalTotalSteps(leave)
	if (leave.status === "approved") return total

	return Math.min(Math.max(0, Number(leave.approval_completed_steps) || 0), total)
}

const approvalProgressPercent = (leave: LeaveRow): number => {
	const total = approvalTotalSteps(leave)

	return total === 0 ? 0 : Math.round((approvalCompletedSteps(leave) / total) * 100)
}

const approvalStatusLabel = (leave: LeaveRow): string => {
	if (leave.status === "approved") return ctrans("Completed")
	if (leave.status === "rejected") return ctrans("Rejected")

	return ctrans("In Progress")
}

const approvalProgressText = (leave: LeaveRow): string => {
	const total = approvalTotalSteps(leave)
	if (total === 0) return ctrans("No steps")
	if (leave.status === "approved") return ctrans(":steps steps completed", { steps: String(total) })

	const currentStep = Math.min(Math.max(1, Number(leave.approval_current_step) || approvalCompletedSteps(leave) + 1), total)

	return ctrans("Level :current of :total", { current: String(currentStep), total: String(total) })
}

const approvalBarClass: Record<string, string> = {
	approved: "bg-green-500",
	rejected: "bg-red-500",
	pending: "bg-amber-500",
}

const decidedApprovals = (leave: LeaveRow): LeaveApproval[] => (leave.approvals ?? []).filter((approval) => approval.status !== "pending")

const leaveRequestsUrl = computed(() => {
	const target = props.data?.leave_requests_route

	return target ? route(target.name, target.parameters) : "#"
})

const leaveRequestUrl = (leave: LeaveRow): string => {
	const target = props.data?.leave_requests_route
	if (!target) return "#"

	return route(target.name, { ...target.parameters, status: leave.status, from: leave.start_date, to: leave.end_date ?? leave.start_date })
}
</script>

<template>
	<div class="px-4 py-4">
		<div v-if="!data" class="animate-pulse space-y-4" aria-busy="true">
			<div class="grid grid-cols-2 gap-3 md:grid-cols-5">
				<div v-for="tile in 5" :key="tile" class="h-20 rounded-lg bg-gray-100" />
			</div>
			<div class="grid gap-4 lg:grid-cols-2">
				<div class="h-28 rounded-lg bg-gray-100" />
				<div class="h-28 rounded-lg bg-gray-100" />
			</div>
			<div v-for="card in 3" :key="card" class="h-40 rounded-lg bg-gray-100" />
		</div>

		<div v-else class="space-y-4">
			<div class="flex flex-wrap items-center justify-between gap-3">
				<div class="flex items-center gap-2">
					<label for="employee-leaves-year" class="text-sm font-medium text-gray-700">{{ ctrans("Period") }}</label>
					<Select
						v-model="yearFilter"
						inputId="employee-leaves-year"
						:options="yearOptions"
						optionLabel="label"
						optionValue="value"
						size="small"
						class="w-36 [&.p-focus]:!border-[--app-accent]" />
				</div>
				<Link :href="leaveRequestsUrl">
					<Button type="tertiary" size="s" :label="ctrans('Open in Leave Requests')">
						<template #icon>
							<FontAwesomeIcon :icon="faExternalLinkAlt" class="text-[--app-accent]" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</Link>
			</div>

			<div class="grid grid-cols-2 gap-3 md:grid-cols-5">
				<template v-for="tile in tiles" :key="tile.key">
					<button
						v-if="tile.isFilter"
						type="button"
						class="rounded-lg border bg-white px-4 py-3 text-left transition hover:border-[--app-accent-muted] hover:bg-[--app-accent-soft] focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]"
						:class="statusFilter === tile.key ? 'border-[--app-accent] ring-1 ring-[--app-accent]' : 'border-gray-200'"
						:aria-pressed="statusFilter === tile.key"
						:data-tile="tile.key"
						@click="toggleStatus(tile.key as StatusFilter)">
						<span class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500">
							<FontAwesomeIcon :icon="tile.icon" :class="tile.iconClass" fixed-width aria-hidden="true" />
							{{ tile.label }}
						</span>
						<span class="mt-1 block text-2xl font-semibold tabular-nums" :class="tile.value ? tile.valueClass : 'text-gray-400'">{{ formatDays(tile.value) }}</span>
					</button>
					<div v-else class="rounded-lg border border-gray-200 bg-white px-4 py-3" :data-tile="tile.key">
						<span class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-500">
							<FontAwesomeIcon :icon="tile.icon" :class="tile.iconClass" fixed-width aria-hidden="true" />
							{{ tile.label }}
						</span>
						<span class="mt-1 block text-2xl font-semibold tabular-nums" :class="tile.value ? tile.valueClass : 'text-gray-400'">{{ formatDays(tile.value) }}</span>
					</div>
				</template>
			</div>

			<div class="grid gap-4 lg:grid-cols-2">
				<section class="rounded-lg border border-gray-200 bg-white px-4 py-3">
					<div class="flex flex-wrap items-baseline justify-between gap-x-3">
						<h3 class="text-sm font-semibold text-gray-700">{{ ctrans("Leave balance") }}</h3>
						<span v-if="data.balance?.period_start || data.balance?.period_end" class="text-xs tabular-nums text-gray-500">
							{{ data.balance.period_start ? formatDate(data.balance.period_start) : "…" }} – {{ data.balance.period_end ? formatDate(data.balance.period_end) : "…" }}
						</span>
					</div>
					<dl v-if="data.balance" class="mt-3 grid grid-cols-3 gap-2 text-center sm:grid-cols-5">
						<div v-for="cell in balanceCells" :key="cell.label" class="rounded-lg bg-gray-50 px-2 py-2">
							<dd class="text-xl font-bold tabular-nums" :class="cell.value ? 'text-gray-800' : 'text-gray-400'">{{ formatDays(cell.value) }}</dd>
							<dt class="text-xs text-gray-500">{{ cell.label }}</dt>
						</div>
					</dl>
					<p v-else class="mt-2 text-xs text-gray-400">{{ ctrans("No leave balance for the current contract.") }}</p>
				</section>

				<section class="rounded-lg border border-gray-200 bg-white px-4 py-3">
					<div class="flex flex-wrap items-baseline justify-between gap-x-3">
						<h3 class="text-sm font-semibold text-gray-700">{{ ctrans("By leave type") }}</h3>
						<span class="text-xs tabular-nums text-gray-500">{{ periodLabel }}</span>
					</div>
					<table v-if="data.by_type.length" class="mt-2 w-full text-sm">
						<thead>
							<tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
								<th scope="col" class="py-1 font-medium">{{ ctrans("Type") }}</th>
								<th scope="col" class="py-1 text-right font-medium">{{ ctrans("Approved") }}</th>
								<th scope="col" class="py-1 text-right font-medium">{{ ctrans("Days") }}</th>
								<th scope="col" class="w-1/3 py-1 pl-3"><span class="sr-only">{{ ctrans("Share of approved days") }}</span></th>
							</tr>
						</thead>
						<tbody class="divide-y divide-gray-100">
							<tr v-for="type in data.by_type" :key="type.key">
								<td class="py-1.5">
									<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-1.5 py-0.5 text-xs font-medium" :style="typeTagStyle(type.color_hex)">
										<span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: type.color_hex }" aria-hidden="true" />
										{{ type.label }}
									</span>
								</td>
								<td class="py-1.5 text-right tabular-nums" :class="type.approved ? 'text-gray-900' : 'text-gray-400'">
									{{ locale.number(type.approved) }}<span v-if="type.requests !== type.approved" class="text-gray-400"> / {{ locale.number(type.requests) }}</span>
								</td>
								<td class="py-1.5 text-right tabular-nums" :class="type.approved_days ? 'text-gray-900' : 'text-gray-400'">{{ formatDays(type.approved_days) }}</td>
								<td class="py-1.5 pl-3">
									<div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
										<div class="h-full rounded-full" :style="{ width: typeBarWidth(type.approved_days), backgroundColor: type.color_hex }" />
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<p v-else class="mt-2 text-xs text-gray-400">{{ ctrans("No leave recorded.") }}</p>
				</section>
			</div>

			<div class="flex flex-wrap items-center justify-between gap-3">
				<h3 class="text-sm font-semibold text-gray-700">
					{{ ctrans("Leave requests") }}
					<span class="font-normal tabular-nums text-gray-500">· {{ locale.number(paginationMeta.total ?? rows.length) }}</span>
				</h3>
				<SegmentedToggle v-model="statusFilter" :options="statusOptions" :ariaLabel="ctrans('Status')" />
			</div>

			<div class="transition-opacity" :class="{ 'pointer-events-none opacity-50': isLoading }" :aria-busy="isLoading">
				<div v-if="rows.length === 0" class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
					<FontAwesomeIcon :icon="faCalendarAlt" class="text-3xl text-gray-300" aria-hidden="true" />
					<p class="mt-3 text-sm font-medium text-gray-900">
						<template v-if="data.summary.total > 0">{{ ctrans("No :status leave requests in this period", { status: statusLabel(statusFilter).toLowerCase() }) }}</template>
						<template v-else-if="yearFilter === 'all'">{{ ctrans("No leave recorded.") }}</template>
						<template v-else>{{ ctrans("No leave in :year", { year: String(yearFilter) }) }}</template>
					</p>
					<div v-if="data.summary.total > 0" class="mt-4 flex justify-center">
						<Button type="tertiary" :label="ctrans('Show all statuses')" @click="statusFilter = 'all'">
							<template #icon>
								<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</div>
					<div v-else-if="yearFilter !== 'all'" class="mt-4 flex justify-center">
						<Button type="tertiary" :label="ctrans('Show all time')" @click="yearFilter = 'all'">
							<template #icon>
								<FontAwesomeIcon :icon="faCalendarAlt" class="text-[--app-accent]" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</div>
				</div>

				<div v-else class="flex flex-col gap-4">
					<HrRequestCard v-for="leave in rows" :key="leave.id" :status="leave.status" :statusLabel="statusLabel(leave.status)">
						<template #title>
							<span class="tabular-nums">
								{{ formatDate(leave.start_date) }}
								<template v-if="leave.end_date && leave.end_date !== leave.start_date"> – {{ formatDate(leave.end_date) }}</template>
							</span>
						</template>
						<template #meta>
							<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-1.5 py-0.5 text-xs font-medium" :style="typeTagStyle(leave.type_color_hex)">
								<span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: leave.type_color_hex }" aria-hidden="true" />
								{{ leave.type_label }}
							</span>
							<span class="tabular-nums">{{ daysText(leave.days) }}</span>
							<span v-if="leave.is_half_day" class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">
								{{ ctrans("Half day") }}<template v-if="leave.session && leave.session !== 'Full'"> · {{ leave.session }}</template>
							</span>
						</template>

						<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
							<div class="rounded-md bg-gray-50 px-3 py-2">
								<div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Approval") }}</div>
								<div class="mt-1 flex items-center justify-between gap-2 text-sm">
									<span class="font-medium text-gray-900">{{ approvalStatusLabel(leave) }}</span>
									<span class="tabular-nums text-gray-600">{{ approvalProgressText(leave) }}</span>
								</div>
								<div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
									<div class="h-full rounded-full" :class="approvalBarClass[leave.status] ?? 'bg-gray-400'" :style="{ width: `${approvalProgressPercent(leave)}%` }" />
								</div>
								<ul v-if="decidedApprovals(leave).length" class="mt-2 space-y-0.5 text-xs text-gray-600">
									<li v-for="approval in decidedApprovals(leave)" :key="`${approval.level}-${approval.approver_name}`" class="flex flex-wrap items-center gap-x-1.5">
										<FontAwesomeIcon
											:icon="approval.status === 'rejected' ? faTimes : faCheck"
											:class="approval.status === 'rejected' ? 'text-red-500' : 'text-green-600'"
											fixed-width
											aria-hidden="true" />
										<span>{{ ctrans("Level :level", { level: String(approval.level) }) }}</span>
										<span v-if="approval.approver_name" class="font-medium text-gray-800">{{ approval.approver_name }}</span>
										<span v-if="approval.decided_at" class="tabular-nums text-gray-500">{{ formatDateTime(approval.decided_at) }}</span>
									</li>
								</ul>
								<p v-else-if="leave.status !== 'pending' && (leave.approver_name || leave.approved_at)" class="mt-2 text-xs text-gray-600">
									<span v-if="leave.approver_name" class="font-medium text-gray-800">{{ leave.approver_name }}</span>
									<span v-if="leave.approver_name && leave.approved_at" class="text-gray-300"> · </span>
									<span v-if="leave.approved_at" class="tabular-nums">{{ formatDateTime(leave.approved_at) }}</span>
								</p>
							</div>

							<dl class="space-y-2 text-sm">
								<div v-if="leave.reason">
									<dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Reason") }}</dt>
									<dd class="whitespace-pre-line break-words text-gray-700">{{ leave.reason }}</dd>
								</div>
								<div v-if="leave.status === 'rejected' && leave.rejection_reason">
									<dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Rejection reason") }}</dt>
									<dd class="mt-1 whitespace-pre-line break-words rounded border border-red-200 bg-red-50 px-3 py-2 text-red-900">{{ leave.rejection_reason }}</dd>
								</div>
								<div v-if="leave.cover_employee_name">
									<dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Covered by") }}</dt>
									<dd class="flex items-center gap-1.5 text-gray-700">
										<FontAwesomeIcon :icon="faUserFriends" class="text-gray-400" fixed-width aria-hidden="true" />
										{{ leave.cover_employee_name }}
									</dd>
								</div>
								<div v-if="leave.attachments?.length">
									<dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Attachments") }}</dt>
									<dd class="mt-1 flex flex-wrap gap-1.5">
										<a
											v-for="attachment in leave.attachments"
											:key="attachment.id"
											:href="attachment.url"
											target="_blank"
											rel="noopener"
											class="inline-flex items-center gap-1 rounded bg-[--app-accent-soft] px-2 py-0.5 text-xs font-medium text-[--app-accent-strong] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]">
											<FontAwesomeIcon :icon="faPaperclip" fixed-width aria-hidden="true" />
											{{ attachment.name }}
										</a>
									</dd>
								</div>
							</dl>
						</div>

						<template #actions>
							<Link :href="leaveRequestUrl(leave)">
								<Button type="tertiary" size="xs" :label="ctrans('Open in Leave Requests')">
									<template #icon>
										<FontAwesomeIcon :icon="faExternalLinkAlt" class="text-[--app-accent]" fixed-width aria-hidden="true" />
									</template>
								</Button>
							</Link>
						</template>
					</HrRequestCard>
				</div>

				<Pagination
					v-if="rows.length > 0 && Number(paginationMeta?.total ?? 0) > 15"
					:on-click="visitPage"
					:has-data="true"
					:meta="paginationMeta"
					:on-per-page-change="changePerPage"
					custom-wrapper-class="mt-4 rounded-lg border border-gray-200" />
			</div>
		</div>
	</div>
</template>

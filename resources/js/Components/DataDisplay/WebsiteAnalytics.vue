<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faArrowUp,
	faArrowDown,
	faHandSparkles,
	faEnvelope,
	faUser,
	faHdd,
	faCloudDownload,
} from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import Chart from "primevue/chart"
import { onMounted, Ref, ref } from "vue"
import { useFormatTime } from "../../Composables/useFormatTime"
import { router } from "@inertiajs/vue3"
import SelectButton from "primevue/selectbutton"
import OverviewCard from "./OverviewCard.vue"

library.add(faArrowUp, faArrowDown, faHandSparkles, faEnvelope, faUser, faHdd, faCloudDownload)

const props = defineProps<{
	data: {
		data: {
			viewer: {
				zones: Array<{
					zones: Array<{
						dimensions: { timeslot: string }
						sum: {
							requests: number
							cachedRequests?: number
							bytes: number
							cachedBytes?: number
							encryptedRequests?: number
							encryptedBytes?: number
						}
						uniq: { uniques: number }
					}>
				}>
			}
		}
	}
}>()

interface SummaryMetric {
	id: number
	label: string
	value: string
	icon: string
	change: string
	changeType: "increase" | "decrease"
}

const reloadData = (until: string, since: string) => {
	router.reload({
		data: { until, since },
		only: ["analytics"],
		onSuccess: () => {
			setChartDataAndOptions()
		},
	})
}

const summaryMetrics: Ref<SummaryMetric[]> = ref([])
const value = ref("24 Hours")
const options = ref(["24 Hours", "7 Days", "30 Days"])
const metrics = [
	{ label: "Unique Visitors", dataKey: "uniques", color: "--p-cyan-500" },
	{ label: "Total Requests", dataKey: "requests", color: "--p-blue-500" },
]
const chartsData = ref([])
const chartsOptions = ref([])

const handleSelectChange = () => {
	const today = new Date()
	let until: string | undefined
	let since: string | undefined

	if (value.value === "7 Days") {
		const sevenDaysAgo = new Date(today)
		sevenDaysAgo.setDate(today.getDate() - 7)
		since = sevenDaysAgo.toISOString().split("T")[0]
		until = today.toISOString().split("T")[0]
	} else if (value.value === "30 Days") {
		const thirtyDaysAgo = new Date(today)
		thirtyDaysAgo.setDate(today.getDate() - 30)
		since = thirtyDaysAgo.toISOString().split("T")[0]
		until = today.toISOString().split("T")[0]
	}

	if (since && until) {
		reloadData(until, since)
	} else {
		reloadData(undefined, undefined)
	}
}

const setChartDataAndOptions = () => {
	if (!props.data.data.viewer.zones || props.data.data.viewer.zones.length === 0) {
		// Set empty data if zones are empty
		summaryMetrics.value = []
		chartsData.value = []
		return
	}

	const optionsTime = { formatTime: "hm" }
	const mainZone = props.data.data.viewer.zones[0]
	const dates = mainZone.zones.map((item) => useFormatTime(item.dimensions.timeslot, optionsTime))

	chartsData.value = metrics.map((metric) => {
		const data = mainZone.zones.map((item) => {
			let value = metric.dataKey === "uniques" ? item.uniq.uniques : item.sum[metric.dataKey]
			if (metric.dataKey === "bytes" || metric.dataKey === "cachedBytes") {
				value = value ? value / 1024 ** 3 : 0
			}
			if (metric.dataKey === "cachedRequests") {
				value = ((item.sum.cachedRequests || 0) / (item.sum.requests || 1)) * 100
			}
			return value
		})

		return {
			labels: dates,
			datasets: [
				{
					label: metric.label,
					data: data,
					fill: true,
					borderColor:
						getComputedStyle(document.documentElement).getPropertyValue(metric.color) ||
						"#00bcd4",
					backgroundColor: `${getComputedStyle(document.documentElement).getPropertyValue(
						metric.color
					)}33`,
					tension: 0.4,
				},
			],
		}
	})

	const textColor =
		getComputedStyle(document.documentElement).getPropertyValue("--p-text-color") || "#333"
	const surfaceBorder =
		getComputedStyle(document.documentElement).getPropertyValue("--p-content-border-color") ||
		"#ddd"

	chartsOptions.value = metrics.map(() => ({
		maintainAspectRatio: false,
		plugins: {
			legend: { display: false },
		},
		scales: {
			x: {
				ticks: { color: textColor },
				grid: { color: surfaceBorder },
			},
			y: {
				ticks: { color: textColor },
				grid: { color: surfaceBorder },
			},
		},
	}))

	const totalRequests = mainZone.zones.reduce((sum, day) => sum + day.sum.requests, 0)
	const cachedRequests = mainZone.zones.reduce(
		(sum, day) => sum + (day.sum.cachedRequests || 0),
		0
	)
	const totalBytes = mainZone.zones.reduce((sum, day) => sum + day.sum.bytes, 0)
	const uniqueVisitors = mainZone.zones.reduce((sum, item) => sum + (item.uniq?.uniques || 0), 0)

	summaryMetrics.value = [
		{
			id: 1,
			label: "Unique Visitors",
			value: uniqueVisitors.toLocaleString(),
			icon: "fal fa-eye",
			change: "",
			changeType: "",
		},
		{
			id: 2,
			label: "Total Requests",
			value: totalRequests.toLocaleString(),
			icon: "fal fa-chart-line",
			change: "",
			changeType: "",
		}
		
		
	]
}

onMounted(() => {
	setChartDataAndOptions()
})
</script>

<template>
	<div class="px-5 py-4">
		<!-- Display header and SelectButton only if data is available -->
		<div v-if="props.data.data.viewer.zones && props.data.data.viewer.zones.length > 0">
			<h3 class="text-base font-semibold leading-6 text-gray-900">Overview (Last 24 Hours)</h3>
			<div class="p-3">
				<SelectButton
					v-model="value"
					:options="options"
					size="small"
					@change="handleSelectChange" />
			</div>
		</div>

		<!-- Conditional content based on data availability -->
		<div v-if="props.data.data.viewer.zones && props.data.data.viewer.zones.length > 0">
			<!-- Summary metrics -->
			<div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
				<OverviewCard
					v-for="metric in summaryMetrics"
					:key="metric.id"
					:label="metric.label"
					:value="metric.value"
					:icon="metric.icon" />
			</div>

			<!-- Charts -->
			<div v-for="(chartData, index) in chartsData" :key="index" class="mt-8">
				<h4 class="text-gray-700 mb-2">{{ metrics[index].label }}</h4>
				<Chart type="line" :data="chartData" :options="chartsOptions[index]" class="h-36" />
			</div>
		</div>
		
		<!-- No data available message -->
		<div v-else class="mt-5 text-gray-500">No data available for the selected period.</div>
	</div>
</template>

<style scoped>
h4 {
	font-size: 1rem;
	font-weight: 500;
}
</style>

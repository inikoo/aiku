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

// Metrics for display in summary and charts
const summaryMetrics: Ref<SummaryMetric[]> = ref([])
const value = ref("24 Hours")
const options = ref(["24 Hours", "7 Days", "30 Days"])
const metrics = [
	{ label: "Unique Visitors", dataKey: "uniques", color: "--p-cyan-500" },
	{ label: "Total Requests", dataKey: "requests", color: "--p-blue-500" },
	{ label: "Percent Cached", dataKey: "cachedRequests", color: "--p-green-500", unit: "%" },
	{ label: "Total Data Served (GB)", dataKey: "bytes", color: "--p-purple-500", unit: "GB" },
	{ label: "Data Cached (GB)", dataKey: "cachedBytes", color: "--p-orange-500", unit: "GB" },
]
const chartsData = ref([])
const chartsOptions = ref([])

const handleSelectChange = () => {
	const today = new Date()
	let until: string | undefined
	let since: string | undefined

	// Determine the date range based on the selected value
	if (value.value === "7 Days") {
		const sevenDaysAgo = new Date(today)
		sevenDaysAgo.setDate(today.getDate() - 7)
		since = sevenDaysAgo.toISOString().split("T")[0]
		until = today.toISOString().split("T")[0] // Set until to today’s date
	} else if (value.value === "30 Days") {
		const thirtyDaysAgo = new Date(today)
		thirtyDaysAgo.setDate(today.getDate() - 30)
		since = thirtyDaysAgo.toISOString().split("T")[0]
		until = today.toISOString().split("T")[0] // Set until to today’s date
	}

	// Reload data with or without date parameters based on selection
	if (since && until) {
		// If we have since and until, pass them to reloadData
		reloadData(until, since)
	} else {
		// If "24 Hours" is selected, reload data without any date parameters
		reloadData(undefined, undefined)
	}
}

const setChartDataAndOptions = () => {
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
	const uniqueVisitors = mainZone.totals.reduce((sum, item) => sum + (item.uniq?.uniques || 0), 0)

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
		},
		{
			id: 3,
			label: "Percent Cached",
			value: `${((cachedRequests / totalRequests) * 100).toFixed(2)}%`,
			icon: "fal fa-database",
			change: "",
			changeType: "",
		},
		{
			id: 4,
			label: "Total Data Served",
			value: `${(totalBytes / 1024 ** 3).toFixed(2)} GB`,
			icon: "fal fa-hdd",
			change: "",
			changeType: "",
		},
		{
			id: 5,
			label: "Data Cached",
			value: `${((cachedRequests * (totalBytes / totalRequests)) / 1024 ** 3).toFixed(2)} GB`,
			icon: "fal fa-cloud-download",
			change: "",
			changeType: "",
		},
	]
}

onMounted(() => {
	setChartDataAndOptions()
})
</script>

<template>
	<div class="px-5 py-4">
		<h3 class="text-base font-semibold leading-6 text-gray-900">Overview (Last 24 Hours)</h3>
		<div class="p-3">
			<SelectButton
				v-model="value"
				:options="options"
				size="small"
				@change="handleSelectChange" />
		</div>
		<div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
			<OverviewCard
				v-for="metric in summaryMetrics"
				:key="metric.id"
				:label="metric.label"
				:value="metric.value"
				:icon="metric.icon" />
		</div>

		<!-- Display each chart in a stacked view -->
		<div v-for="(chartData, index) in chartsData" :key="index" class="mt-8">
			<h4 class="text-gray-700 mb-2">{{ metrics[index].label }}</h4>
			<Chart type="line" :data="chartData" :options="chartsOptions[index]" class="h-36" />
		</div>
	</div>
</template>

<style scoped>
h4 {
	font-size: 1rem;
	font-weight: 500;
}
</style>

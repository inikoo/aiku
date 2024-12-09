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
import { onMounted, Ref, ref, watch } from "vue"
import { useFormatTime } from "../../Composables/useFormatTime"
import { router } from "@inertiajs/vue3"
import SelectButton from "primevue/selectbutton"
import MetricCard from "./MetricCard.vue"

library.add(faArrowUp, faArrowDown, faHandSparkles, faEnvelope, faUser, faHdd, faCloudDownload)

const props = defineProps<{
	data: {
		rumAnalyticsTimeseries: {
			data: {
				viewer: {
					accounts: Array<{
						series: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								ts: string
							}
							sum: {
								visits: number
							}
						}>
					}>
				}
			}
		}
		rumAnalyticsTopNs: {
			data: {
				viewer: {
					accounts: Array<{
						countries: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // e.g., "GB" (country code)
							}
						}>
						topBrowsers: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // e.g., "Chrome" (browser name)
							}
						}>
						topDeviceTypes: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // e.g., "desktop", "mobile"
							}
						}>
						topOS: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // e.g., "Windows", "MacOS"
							}
						}>
						topHosts: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // Host information
							}
						}>
						topPaths: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // Path details
							}
						}>
						topReferrers: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
							dimensions: {
								metric: string // Referrer details
							}
						}>
						total: {
							count: number // Total count of visits
							sum: {
								visits: number // Total sum of visits
							}
						}
					}>
				}
			}
		}
		rumSparkline: {
			data: {
				viewer: {
					accounts: Array<{
						cls: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
						}>
						fid: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
						}>
						inp: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
						}>
						lcp: Array<{
							avg: {
								sampleInterval: number
							}
							count: number
						}>
						pageviews: Array<{
							sampleInterval: number
							count: number
							dimensions: {
								ts: string // ISO timestamp
							}
						}>
						pageviewsDelta: {
							count: number
						}
						performance: {
							aggregation: {
								pageLoadTime: number // Total page load time
							}
							avg: {
								sampleInterval: number
							}
							count: number
						}
						performanceDelta: {
							aggregation: {
								pageLoadTime: number
							}
						}
						totalPerformance: {
							aggregation: {
								pageLoadTime: number
							}
						}
						visits: Array<{
							sum: {
								visits: number
							}
						}>
						visitsDelta: {
							sum: {
								visits: number
							}
						}
					}>
				}
			}
		}
		zone: {
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
console.log(props.data.rumAnalyticsTimeseries)

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
const value = ref("24 Hours") // Force to 24 hours by default
const options = ref(["24 Hours", "7 Days", "30 Days"])
const metrics = [
	{ label: "Unique Visitors", dataKey: "uniques", color: "--p-cyan-500" },
	{ label: "Total Requests", dataKey: "requests", color: "--p-blue-500" },
]
const chartsData = ref({})
const chartsOptions = ref({})

const selectedMetric = ref("Unique Visitors")

// Function to format times in HH:mm format
const formatTime = (timestamp: string): string => {
	const date = new Date(timestamp)
	const hours = date.getHours().toString().padStart(2, "0")
	const minutes = date.getMinutes().toString().padStart(2, "0")
	return `${hours}:${minutes}`
}

const handleSelectChange = () => {
	const today = new Date()
	const since = new Date(today)
	since.setHours(today.getHours() - 24) // Get the time 24 hours ago
	const until = today.toISOString().split("T")[0] // Use today's date as "until"

	console.log("Since:", since)
	console.log("Until:", until)

	reloadData(until, since.toISOString().split("T")[0])
}

const setChartDataAndOptions = () => {
	const timeseriesData =
		props.data?.rumAnalyticsTimeseries?.data?.viewer?.accounts[0]?.series || []

	// Prepare labels (timestamps) and data (visits) for the chart
	const labels = timeseriesData.map((item) => {
		const date = new Date(item.dimensions.ts)
		return `${date.getHours().toString().padStart(2, "0")}:${date
			.getMinutes()
			.toString()
			.padStart(2, "0")}`
	})

	const data = timeseriesData.map((item) => item.sum.visits)

	// Update chart data
	chartsData.value = {
		"Analytics Timeseries": {
			labels: labels,
			datasets: [
				{
					label: "Total visits",
					data: data,
					borderColor: "#007bff",
					backgroundColor: "transparent", 
					fill: false,
					tension: 0, 
					borderWidth: 2,
					pointRadius: 0,
				},
			],
		},
	}

	// Update chart options
	chartsOptions.value = {
		"Analytics Timeseries": {
			maintainAspectRatio: false,
			responsive: true,
			plugins: {
				legend: { display: true },
				tooltip: {
					backgroundColor: "#333",
					titleColor: "#fff",
					bodyColor: "#fff",
					borderColor: "#fff",
					borderWidth: 1,
				},
			},
			scales: {
				x: {
					ticks: { color: "#333", font: { size: 12 } },
					grid: { color: "#ddd", borderDash: [4, 2] },
				},
				y: {
					ticks: { color: "#333", font: { size: 12 } },
					grid: { color: "#ddd", borderDash: [4, 2] },
				},
			},
		},
	}

	// Add summary metric for total visits
	const totalVisits = data.reduce((sum, visits) => sum + visits, 0)
	summaryMetrics.value = [
		{
			id: 1,
			label: "Total Visits",
			value: totalVisits.toLocaleString(),
			icon: "fal fa-eye",
			change: "",
			changeType: "",
		},
	]
}

const handleCardClick = (metricLabel: string) => {
	selectedMetric.value = metricLabel
}

onMounted(() => {
	setChartDataAndOptions()
})
watch(value, handleSelectChange)
</script>

<template>
	<div class="min-h-screen bg-gray-100 p-6">
		<!-- Layout -->
		<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
			<!-- Sidebar -->
			<div class="space-y-6">
				<!-- Visits Card -->
				<div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-start">
					<div class="text-lg font-semibold text-gray-700">Visits</div>
					<div class="text-3xl font-bold text-gray-900">230</div>
					<div class="w-full mt-2">
						<OverviewCard
							v-for="metric in summaryMetrics"
							:key="metric.id"
							:label="metric.label"
							:value="metric.value"
							:icon="metric.icon" />
					</div>
				</div>

				<!-- Page Views Card -->
				<div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-start">
					<div class="text-lg font-semibold text-gray-700">Page Views</div>
					<div class="text-3xl font-bold text-gray-900">310</div>
					<div class="w-full mt-2">
						<OverviewCard
							v-for="metric in summaryMetrics"
							:key="metric.id"
							:label="metric.label"
							:value="metric.value"
							:icon="metric.icon" />
					</div>
				</div>

				<!-- Page Load Time Card -->
				<div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-start">
					<div class="text-lg font-semibold text-gray-700">Page Load Time</div>
					<div class="text-3xl font-bold text-gray-900">1,781ms</div>
					<div class="w-full mt-2">
						<OverviewCard
							v-for="metric in summaryMetrics"
							:key="metric.id"
							:label="metric.label"
							:value="metric.value"
							:icon="metric.icon" />
					</div>
				</div>

				<!-- Core Web Vitals -->
				<div class="bg-white rounded-lg shadow-md p-4">
					<div class="text-lg font-semibold text-gray-700 mb-2">Core Web Vitals</div>
					<div class="space-y-2">
						<div class="flex items-center">
							<span class="w-12 font-medium text-gray-600">LCP</span>
							<div class="flex-1 h-4 bg-gray-200 rounded-lg overflow-hidden">
								<div class="h-full bg-green-500" style="width: 85%"></div>
							</div>
						</div>
						<div class="flex items-center">
							<span class="w-12 font-medium text-gray-600">INP</span>
							<div class="flex-1 h-4 bg-gray-200 rounded-lg overflow-hidden">
								<div class="h-full bg-green-500" style="width: 70%"></div>
							</div>
						</div>
						<div class="flex items-center">
							<span class="w-12 font-medium text-gray-600">FID</span>
							<div class="flex-1 h-4 bg-gray-200 rounded-lg overflow-hidden">
								<div class="h-full bg-green-500" style="width: 90%"></div>
							</div>
						</div>
						<div class="flex items-center">
							<span class="w-12 font-medium text-gray-600">CLS</span>
							<div class="flex-1 h-4 bg-gray-200 rounded-lg overflow-hidden">
								<div class="h-full bg-yellow-400" style="width: 50%"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Main Content -->
			<div class="lg:col-span-3">
				<div class="bg-white rounded-lg shadow-md p-6">
					<!-- Title and Filters -->
					<div class="flex justify-between items-center mb-6">
						<div>
							<h2 class="text-lg font-semibold text-gray-700">
								Web Analytics for example.com
							</h2>
							<p class="text-sm text-gray-500">
								Overview of site performance and visitor statistics
							</p>
						</div>
						<div class="flex space-x-4 items-center">
							<button
								class="bg-blue-500 text-white text-sm font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600">
								Add Filter
							</button>
							<select
								class="border border-gray-300 rounded-md py-2 px-4 text-sm text-gray-700">
								<option>Last 24 Hours</option>
								<option>Last 7 Days</option>
								<option>Last 14 Days</option>
							</select>
						</div>
					</div>

					<!-- Graph -->
					<div class="relative">
						<Chart
							type="line"
							:data="chartsData['Analytics Timeseries']"
							:options="chartsOptions['Analytics Timeseries']"
							class="h-96" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.min-h-screen {
	font-family: Arial, sans-serif;
}
</style>

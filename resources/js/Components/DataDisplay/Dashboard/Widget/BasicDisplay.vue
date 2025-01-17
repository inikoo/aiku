<script setup lang="ts">
import CountUp from "vue-countup-v3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faExclamation, faInfo, faPlay } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { inject, ref } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import MeterGroup from "primevue/metergroup"
import { values } from "lodash"
import ChartDashboardDynamic from "../../ChartDashboardDynamic.vue"
import Chart from "primevue/chart"
import ProgressDashboardCard from "../../ProgressDashboardCard.vue"
import { Link } from "@inertiajs/vue3"
library.add(faCheck, faExclamation, faInfo, faPlay)

// Props for dynamic behavior
const props = withDefaults(
	defineProps<{
		showRedBorder: boolean
		widgetData: {
			value: string
			description: string
			status: "success" | "warning" | "danger" | "information" | "neutral"
			type?: "number" | "currency"
			currency_code?: string
			route?: {}
		}
		visual?: any
	}>(),
	{
		widgetData: () => {
			return {
				value: "0",
				description: "",
				status: "information",
			}
		},
	}
)
console.log(props)

// Example data to use in grand parent (Dashboard)
const widgets = {
	column_count: 4,
	components: [
		{
			type: "basic",
			col_span: 1,
			row_span: 2,
			data: {
				value: 0,
				description: "xxxxxxx",
				status: "success",
			},
		},
		{
			type: "basic",
			col_span: 1,
			row_span: 1,
			data: {
				value: 180000,
				description: "ggggggg",
				status: "danger",
				type: "currency",
				currency_code: "GBP",
			},
		},
		{
			type: "basic",
			col_span: 1,
			row_span: 1,
			data: {
				value: 662137,
				description: "ggggggg",
				// 'status': 'information',
				type: "currency",
				currency_code: "GBP",
			},
		},
		{
			type: "basic",
			col_span: 1,
			row_span: 1,
			data: {
				value: 99,
				type: "number",
				description: "Hell owrodl",
				status: "warning",
			},
		},
		{
			type: "basic",
			col_span: 3,
			row_span: 1,
			data: {
				value: 44400,
				description: "6666",
				status: "information",
				// 'status': 'success',
			},
		},
	],
}
/* const value = ref([
    { label: 'Apps', color: '#34d399', value: 16 },
    { label: 'Messages', color: '#fbbf24', value: 8 },
    { label: 'Media', color: '#60a5fa', value: 24 },
    { label: 'System', color: '#c084fc', value: 10 }
]); */
// const getTypeVisualComponent = (type: string) => {
// 	switch (type) {
// 		case "chart":
// 			return Chart
// 		// case "percentage":
// 		// 	return FontAwesomeIcon
// 		// case "chart":
// 		// 	return ChartDashboardDynamic
// 		default:
// 			return null
// 	}
// }

const locale = inject("locale", aikuLocaleStructure)

const getStatusColor = (status: string) => {
	switch (status) {
		case "success":
			return "bg-green-100 border border-green-400 text-green-600"
		case "warning":
			return "bg-yellow-100 border border-yellow-400 text-yellow-600"
		case "danger":
			return "bg-red-100 border border-red-400 text-red-600"
		case "information":
			return "bg-gray-200 border border-gray-400"
		default:
			return "bg-white border border-gray-200"
	}
}

const getIcon = (status?: string) => {
	switch (status) {
		case "success":
			return "fas fa-check"
		case "warning":
			return "fas fa-exclamation"
		case "danger":
			return "fas fa-exclamation"
		case "information":
			return "fas fa-info"
	}
}

const getIconColor = (status?: string) => {
	switch (status) {
		case "success":
			return "bg-green-400 text-white"
		case "warning":
			return "bg-yellow-400 text-white"
		case "danger":
			return "bg-red-400 text-white"
		case "information":
			return "bg-gray-400 text-white"
	}
}

const printLabelByType = (label?: string) => {
	switch (props.widgetData.type) {
		case "currency":
			return locale.currencyFormat(props.widgetData.currency_code || "usd", Number(label))
		default:
			return label
	}
}

function NumberDashboard(shop: any) {
	console.log(shop)
	return route(shop?.name, shop?.parameters)
}
// const chartLabels = ["1", "2", "3", "4", "5", "6", "7", "8"]
// const chartData = [10, 20, 15, 25, 20, 18, 22, 10]
// const dummyChartData = {
// 	labels: ['A', 'B', 'C'],
// 	datasets: [
// 		{
// 			data: [540, 325, 702],
// 			// backgroundColor: [documentStyle.getPropertyValue('--p-cyan-500'), documentStyle.getPropertyValue('--p-orange-500'), documentStyle.getPropertyValue('--p-gray-500')],
// 			// hoverBackgroundColor: [documentStyle.getPropertyValue('--p-cyan-400'), documentStyle.getPropertyValue('--p-orange-400'), documentStyle.getPropertyValue('--p-gray-400')]
// 		}
// 	]
// }
</script>

<template>
	<div :class="['rounded-lg p-6 shadow-md relative h-full', getStatusColor(widgetData.status)]">
		<p
			v-tooltip="printLabelByType(widgetData?.value)"
			class="text-4xl font-bold leading-tight truncate">
			<!-- Render CountUp if widgetData.type is 'number' -->
			<template v-if="widgetData?.type === 'number'">
				<template v-if="widgetData?.route">
					<Link :href="NumberDashboard(widgetData.route)">
						<CountUp
							class="primaryLink inline-block"
							:endVal="widgetData?.value"
							:duration="1.5"
							:scrollSpyOnce="true"
							:options="{
            formattingFn: (value: number) => locale.number(value)
          }" />
					</Link>
				</template>
				<template v-else>
					<CountUp
						:endVal="widgetData?.value"
						:duration="1.5"
						:scrollSpyOnce="true"
						:options="{
          formattingFn: (value: number) => locale.number(value)
        }" />
				</template>
			</template>

			<template v-else>
				<template v-if="widgetData?.route">
					<Link :href="NumberDashboard(widgetData.route)" class="primaryLink">
						{{ printLabelByType(widgetData?.value) }}
					</Link>
				</template>
				<template v-else>
					{{ printLabelByType(widgetData?.value) }}
				</template>
			</template>
		</p>

		<p class="text-base text-gray-500">{{ widgetData.description }}</p>

		<!-- Visual Progress Bar -->
		<div v-if="visual?.type === 'MeterGroup'" class="mt-3">
			<ProgressDashboardCard
				:progressBar="{
					value: visual.value,
					max: visual.max,
					color: visual.color,
					routeDashboard: visual.route,
					right_label: visual.right_label,
					label: visual.label,
				}" />
		</div>

		<!-- Visual Percentage -->
		<div v-if="visual?.type === 'percantage'" class="flex items-center space-x-2">
			<!-- Percentage Value -->
			<span class="text-base font-medium leading-none">
				{{
					visual?.value
						? `${visual?.value > 0 ? "+" : ""}${visual?.value.toFixed(2)}%`
						: `0.0%`
				}}
			</span>

			<!-- FontAwesome Icon -->
			<FontAwesomeIcon
				v-if="visual?.value"
				:icon="visual?.value < 0 ? 'fas fa-play' : 'fas fa-play'"
				class="text-xl mt-1"
				:class="
					visual?.value < 0 ? 'text-red-500 rotate-90' : 'text-green-500 rotate-[-90deg]'
				" />
		</div>

		<div v-if="visual?.type === 'number'" class="mt-2">
			<span class="text-2xl font-bold leading-tight truncate">
				<Link :href="NumberDashboard(visual.route)">
					<CountUp
						class="primaryLink w-10"
						v-if="visual.type === 'number'"
						:endVal="visual.value"
						:duration="1.5"
						:scrollSpyOnce="true"
						:options="{
                    formattingFn: (value: number) => locale.number(value)
                }" />
				</Link>
			</span>
		</div>
		<Chart
			v-else-if="visual?.type == 'pie'"
			type="pie"
			:labels="'visual?.label'"
			:data="visual.value" />

		<Chart
			v-else-if="visual?.type == 'bar'"
			type="bar"
			:labels="'visual?.label'"
			:data="visual.value" />

		<Chart
			v-else-if="visual?.type == 'line'"
			type="line"
			:labels="'visual?.value'"
			:data="visual.value" />

		<Chart
			v-else-if="visual?.type == 'doughnut'"
			type="doughnut"
			:labels="'visual?.label'"
			:data="visual.value" />

		<!-- <div v-if="visual?.type === 'chart'">
			<ChartDashboardDynamic :labels="visual?.label" :dataset="visual.value" lineColor="#00D8FF" />
		</div> -->

		<!-- Conditional Red Exclamation Icon -->
		<div
			v-if="getIcon(widgetData.status)"
			class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 rounded-full w-6 h-6 text-xs flex items-center justify-center shadow-md"
			:class="getIconColor(widgetData.status)">
			<FontAwesomeIcon
				v-if="getIcon(widgetData.status)"
				:icon="getIcon(widgetData.status)"
				fixed-width
				aria-hidden="true" />
		</div>
	</div>
</template>
<style scoped lang="scss"></style>

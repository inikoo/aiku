<script setup lang="ts">
import CountUp from "vue-countup-v3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faExclamation, faInfo, faPlay } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { inject, computed } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import Chart from "primevue/chart"
import { Link } from "@inertiajs/vue3"
import { layoutStructure } from "@/Composables/useLayoutStructure"
library.add(faCheck, faExclamation, faInfo, faPlay)

const props = withDefaults(
	defineProps<{
		widget: any // object or array of objects
		visual?: any // chart configuration object or array of configurations
	}>(),
	{
		widget: () => ({
			value: "0",
			description: "",
			status: "information",
			type: "number",
		}),
	}
)
console.log(props)

const locale = inject("locale", aikuLocaleStructure)
const layoutStore = inject("layout", layoutStructure)

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

const printLabelByType = (label?: string, type?: string, currency_code?: string) => {
	switch (type) {
		case "currency":
			return locale.currencyFormat(currency_code || "usd", Number(label))
		default:
			return label
	}
}

function NumberDashboard(shop: any) {
	console.log(shop)
	return route(shop?.name, shop?.parameters)
}

const widgetArray = computed(() => {
	return Array.isArray(props.widget) ? props.widget : [props.widget]
})

const visualArray = computed(() => {
	if (!props.visual) return []
	return Array.isArray(props.visual) ? props.visual : [props.visual]
})

const setChartOptions = () => ({
	responsive: true,
	maintainAspectRatio: false,
	plugins: {
		legend: {
			display: false,
		},
		tooltip: {
			callbacks: {
				label: (context) => {
					const value = parseFloat(context.parsed.y ?? context.parsed) || 0
					// If a currency code is provided in the chart config, use it.
					const currencyCode = visualArray.value[context.dataIndex]?.currency_codes
					if (currencyCode) {
						return locale.currencyFormat(currencyCode, value)
					}
					return locale.number(value)
				},
			},
		},
	},
})
</script>

<template>
	<div
		v-for="(item, index) in widgetArray"
		:key="index"
		:class="['rounded-lg p-4 shadow-md relative h-full', getStatusColor(item.status)]">
		<!-- Widget Header -->
		<p class="text-2xl font-bold leading-tight truncate">
			<template v-if="item.type === 'number'">
				<template v-if="item.route">
					<Link :href="NumberDashboard(item.route)">
						<CountUp
							class="primaryLink inline-block"
							:endVal="item.value"
							:duration="1.5"
							:scrollSpyOnce="true"
							:options="{ formattingFn: (value: number) => locale.number(value) }" />
					</Link>
				</template>
				<template v-else>
					<CountUp
						:endVal="item.value"
						:duration="1.5"
						:scrollSpyOnce="true"
						:options="{ formattingFn: (value: number) => locale.number(value) }" />
				</template>
			</template>
			<template v-else>
				<template v-if="item.route">
					<Link :href="NumberDashboard(item.route)" class="primaryLink">
						{{ printLabelByType(item.value, item.type, item.currency_code) }}
					</Link>
				</template>
				<template v-else>
					{{ printLabelByType(item.value, item.type, item.currency_code) }}
				</template>
			</template>
		</p>
		<p class="text-sm text-gray-500">{{ item.description }}</p>

		<!-- If a visual configuration exists for this widget, show its chart -->
		<div v-if="visualArray.length > index" class="mt-4">
			<Chart
				:type="visualArray[index].type"
				:labels="visualArray[index].label"
				:data="visualArray[index].value"
				:height="200"
				:options="setChartOptions()" />
		</div>
	</div>
</template>

<style scoped lang="scss">
.chart-container {
	flex-grow: 1;
	position: relative;
}

.chart-container canvas {
	display: block;
	width: 100%;
	height: 100% !important;
}

.bottom-content {
	display: flex;
	align-items: flex-end;
	justify-content: center;
	height: 100%;
}
</style>

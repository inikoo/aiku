<script setup>
import { ref, watch } from "vue"
import Chart  from "primevue/chart"

const props = defineProps({
	labels: {
		type: Array,
		required: true,
	},
	dataset: {
		type: Array,
		required: true,
	},
	lineColor: {
		type: String,
		default: "#00D8FF",
	},
})

const chartData = ref(null)
const chartOptions = ref(null)

watch([() => props.labels, () => props.dataset], () => {
	setChartData()
})

const setChartData = () => {
	chartData.value = {
		labels: props.labels,
		datasets: [
			{
				label: "",
				data: props.dataset,
				borderColor: props.lineColor,
				borderWidth: 2,
				fill: false,
				tension: 0,
			},
		],
	}
}

const setChartOptions = () => {
	chartOptions.value = {
		plugins: {
			legend: {
				display: false,
			},
		},
		scales: {
			x: {
				display: false,
			},
			y: {
				display: false,
			},
		},
		elements: {
			point: {
				radius: 0,
			},
		},
		responsive: true,
		maintainAspectRatio: false,
	}
}

setChartOptions()
setChartData()
</script>

<template>
	<div >
		<Chart type="line" :data="chartData" :options="chartOptions" class="h-16" />
	</div>
</template>

<style scoped>
/* Additional styling if needed */
</style>

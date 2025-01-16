<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"

import { library } from "@fortawesome/fontawesome-svg-core"
import { faTruckCouch } from "@fal"
import { capitalize } from "@/Composables/capitalize"
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from "chart.js"
import { ref, computed } from "vue"
import Dashboard from "@/Components/DataDisplay/Dashboard/Dashboard.vue"

library.add(faTruckCouch)
ChartJS.register(ArcElement, Tooltip, Legend, Colors)

// Define Props
const props = defineProps<{
	title: string
	pageHead: any // Adjust to your actual type
	flatTreeMaps: {}
	dashboard_stats: {}
}>()



</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />

	<div class="grid grid-cols-12 m-3 gap-4">
		<div class="col-span-12">
			<Dashboard
				:dashboard="dashboard_stats"
			/>
		</div>
		
	</div>
</template>

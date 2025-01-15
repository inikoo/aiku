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
	dashboard_stats: {
		customers: {
			active: { label: string; count: number }
			inactive: { label: string; count: number }
		}
		pallet_deliveries: { label: string; count: number }
		pallet_returns: { label: string; count: number }
		pallets: {
			all: { label: string; count: number }
			pallets_with_stored_items: { label: string; count: number }
			pallets_type_pallet: { label: string; count: number }
			pallets_type_box: { label: string; count: number }
			pallets_type_oversize: { label: string; count: number }
		}
		stored_items: { label: string; count: number }
		recurring_bills: {
			all: { label: string; count: number }
			current: { label: string; count: number }
			former: { label: string; count: number }
		}
	}
}>()

// Process customer data
const customerCards = computed(() => {
	const { customers } = props.stats

	return [
		{
			value: customers.active.count.toString(),
			description: customers.active.label,
			showRedBorder: customers.active.count < 5,
			showIcon: customers.active.count === 0,
		},
		{
			value: customers.inactive.count.toString(),
			description: customers.inactive.label,
			showRedBorder: false,
			showIcon: customers.inactive.count > 50,
		},
	]
})

// Process pallet data
const palletCards = computed(() => {
	const { pallets } = props.stats

	return [
		{
			value: pallets.all.count.toString(),
			description: pallets.all.label,
			showRedBorder: false,
			showIcon: false,
		},
		{
			value: pallets.pallets_with_stored_items.count.toString(),
			description: pallets.pallets_with_stored_items.label,
			showRedBorder: pallets.pallets_with_stored_items.count === 0,
			showIcon: pallets.pallets_with_stored_items.count === 0,
		},
		{
			value: pallets.pallets_type_pallet.count.toString(),
			description: pallets.pallets_type_pallet.label,
			showRedBorder: false,
			showIcon: false,
		},
		{
			value: pallets.pallets_type_box.count.toString(),
			description: pallets.pallets_type_box.label,
			showRedBorder: pallets.pallets_type_box.count < 10,
			showIcon: pallets.pallets_type_box.count === 0,
		},
		{
			value: pallets.pallets_type_oversize.count.toString(),
			description: pallets.pallets_type_oversize.label,
			showRedBorder: false,
			showIcon: false,
		},
	]
})
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

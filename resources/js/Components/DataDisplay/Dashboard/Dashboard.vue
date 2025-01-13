<script setup lang="ts">
import DashboardSettings from "./DashboardSettings.vue"
import DashboardTable from "./DashboardTable.vue"
import DashboardWidget from "./DashboardWidget.vue"
import { inject, ref, computed, provide } from "vue"

const props = defineProps<{

	dashboard: {
		settings:{}[]
		interval_options: Array<{ label: string; value: string }>
		table:{}[]
		total:{}[]
		widgets:{}[]
	}
}>()

const layout = inject("layout")
const locale = inject("locale")
console.log(props,'propssasas');

const selectedDateOption = ref(props.dashboard.settings.selected_interval || "ytd")

const currency = ref([
	{ name: "Group", code: "grp", symbol: props.data?.currency?.symbol },
	{ name: "Organisation", code: "org", symbol: null },
])

const isOrganisation = ref(false)

const selectedCurrency = computed(() => {
	return isOrganisation.value ? currency.value[1] : currency.value[0]
})

// Compute table data dynamically
const tableDatas = computed(() => {
	return props.dashboard.table
		.filter((org) => org.type !== "agent")
		.map((org) => ({
			name: org.name,
			code: org.code,
			interval_percentages: org.interval_percentages,
			sales: org.sales || 0,
			currency: org.currency_code ,
		}))
})

const toggleCurrency = () => {
	isOrganisation.value = !isOrganisation.value
}

// const updateRouteAndUser = async (interval) => {
// 	selectedDateOption.value = interval
// 	try {
// 		const response = await axios.patch(route("grp.models.user.update", layout.user.id), {
// 			settings: { selected_interval: interval },
// 		})
// 		console.log("Update successful:", response.data)
// 	} catch (error) {
// 		console.error("Error updating user:", error.response?.data || error.message)
// 	}
// }

const organisationSymbols = computed(() => {
	
})
</script>



<template>
	<div>
		<DashboardSettings
			:dashboard="data"
			:selectedDateOption="selectedDateOption"
			:isOrganisation="isOrganisation"
			:organisationSymbols="organisationSymbols"
			@toggle-currency="toggleCurrency"
			:intervalOptions="props.dashboard?.interval_options"
			:settings="props.dashboard?.settings"
		/>
		
		<DashboardTable
			:tableData="tableDatas"
			:locale="locale"
			:totalAmount="props.dashboard.total"
			:selectedDateOption="props.dashboard.settings.selected_interval"
		/>

		<DashboardWidget :widgetsData="dashboard.widgets" />
	</div>
</template>


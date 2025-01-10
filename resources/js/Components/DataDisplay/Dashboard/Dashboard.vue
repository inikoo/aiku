<script setup lang="ts">
import DashboardSettings from "./DashboardSettings.vue"
import DashboardTable from "./DashboardTable.vue"
import DashboardWidget from "./DashboardWidget.vue"
import { inject, ref, computed } from "vue"

const props = defineProps<{
	data: {
		currency: { symbol: string; code: string }
		organisations: Array<{
			name: string
			code: string
			type: string
			sales: number
			interval_percentages?: any
			currency: { code: string; symbol: string }
		}>
	}
	dashboard: any
	interval_options: Array<{ label: string; value: string }>
}>()

const layout = inject("layout")
const locale = inject("locale")

const selectedDateOption = ref(props.dashboard.settings.selected_interval || "ytd")

const currency = ref([
	{ name: "Group", code: "grp", symbol: props.data.currency.symbol },
	{ name: "Organisation", code: "org", symbol: null },
])

const isOrganisation = ref(false)

const selectedCurrency = computed(() => {
	return isOrganisation.value ? currency.value[1] : currency.value[0]
})

// Compute table data dynamically
const tableDatas = computed(() => {
	return props.data.organisations
		.filter((org) => org.type !== "agent")
		.map((org) => ({
			name: org.name,
			code: org.code,
			interval_percentages: org.interval_percentages,
			sales: org.sales || 0,
			currency: isOrganisation.value ? org.currency.code : props.data.currency.code,
		}))
})

const toggleCurrency = () => {
	isOrganisation.value = !isOrganisation.value
}

const updateRouteAndUser = async (interval) => {
	selectedDateOption.value = interval
	try {
		const response = await axios.patch(route("grp.models.user.update", layout.user.id), {
			settings: { selected_interval: interval },
		})
		console.log("Update successful:", response.data)
	} catch (error) {
		console.error("Error updating user:", error.response?.data || error.message)
	}
}

const organisationSymbols = computed(() => {
	const symbols = props.data.organisations
		.filter((org) => org.type !== "agent")
		.map((org) => org.currency.symbol)
		.filter(Boolean)
	return [...new Set(symbols)].join(" / ")
})
</script>

<template>
	<div>
		<DashboardSettings
			:dashboard="data"
			:intervalOptions="interval_options"
			:selectedDateOption="selectedDateOption"
			:isOrganisation="isOrganisation"
			:organisationSymbols="organisationSymbols"
			@toggle-currency="toggleCurrency"
			@update-interval="updateRouteAndUser" />
		<DashboardTable
			:tableData="tableDatas"
			:groupStats="data"
			:locale="locale"
			:selectedDateOption="selectedDateOption" />
		<DashboardWidget :widgetsData="dashboard.widgets" />
	</div>
</template>

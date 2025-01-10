<script setup lang="ts">
import DashboardSettings from "./DashboardSettings.vue"
import DashboardTable from "./DashboardTable.vue"
import DashboardWidget from "./DashboardWidget.vue"
import { inject, ref, computed } from "vue"

const props = defineProps<{
	data: {
		organisations: {}[]
	}
	dashboard: {}
	interval_options: {}
}>()

const layout = inject("layout")
const locale = inject("locale")

const selectedDateOption = ref(props.dashboard.settings.selected_interval || "ytd")

const currency = ref([
	{ name: "Group", code: "grp", symbol: props.data.currency.symbol },
	{ name: "Organisation", code: "org", symbol: null },
])

const selectedCurrency = ref(currency.value[0])
const isOrganisation = ref(selectedCurrency.value.code === "org")

const toggleCurrency = () => {
	selectedCurrency.value = isOrganisation.value ? currency.value[1] : currency.value[0]
}
const tableDatas = computed(() => {
	return props.data?.organisations
		.filter((org) => org.type !== "agent")
		.map((org) => {
			return {
				name: org.name,
				code: org.code,
				interval_percentages: org.interval_percentages,
				sales: org.sales || 0,
				currency:
					selectedCurrency.value.code === "grp"
						? props.data.currency.code
						: org.currency.code,
			}
		})
})

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
</script>
<template>
	<div>
		<DashboardSettings :dashboard="props.data" :intervalOptions="interval_options"
		:selectedDateOption="selectedDateOption" :isOrganisation="isOrganisation"
		@toggle-currency="toggleCurrency" @update-interval="updateRouteAndUser"" />
		<DashboardTable
			:tableData="tableDatas"
			:groupStats="data"
			:locale="locale"
			:selectedDateOption="selectedDateOption" />
		<DashboardWidget :widgetsData="dashboard.widgets" />
	</div>
</template>
<style scoped></style>

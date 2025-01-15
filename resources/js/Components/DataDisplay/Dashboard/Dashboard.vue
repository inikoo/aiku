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


const isOrganisation = ref(false)


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


</script>

<template>
	<div>
		
		<DashboardSettings
		v-if="props.dashboard.settings"
			@toggle-currency="toggleCurrency"
			:intervalOptions="props.dashboard?.interval_options"
			:settings="props.dashboard?.settings"
		/>
		
		<DashboardTable
			v-if="props.dashboard.table"
			:tableData="tableDatas"
			:locale="locale"
			:totalAmount="props.dashboard.total"
			:selectedDateOption="props.dashboard.settings.selected_interval"
		/>

		<DashboardWidget 
		v-if="props.dashboard.widgets"
		:widgetsData="dashboard.widgets" />
	</div>
</template>



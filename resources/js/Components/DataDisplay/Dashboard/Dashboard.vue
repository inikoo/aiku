<script setup lang="ts">
import DashboardSettings from "./DashboardSettings.vue"
import DashboardTable from "./DashboardTable.vue"
import DashboardWidget from "./DashboardWidget.vue"
import { inject, ref, computed, provide } from "vue"

const props = defineProps<{
	dashboard?: {
		settings?: {}[]
		interval_options?: Array<{ label: string; value: string }>
		table?: {}[]
		total?: {}[]
		widgets?: {}[]
	}
	checked?: boolean
	tableType?: string
}>()

const layout = inject("layout")
const locale = inject("locale")
const checked = ref(props.checked || false)

const onToggleChecked = (value: boolean) => {
	checked.value = value
}

const isOrganisation = ref(false)
/* .filter((org) => {
			if (props.dashboard.settings.key_shop) {
                return org.state !== "closed";
            }
            return true;
		}) */

const tableDatas = computed(() => {
    const isShopOpen = props.dashboard.settings.db_settings.selected_shop_open === "open";

    if (props.tableType === "org") {
        return props.dashboard.table
            .filter((item) => {
                return isShopOpen ? item.state !== "closed" : true;
            })
            .map((org) => ({
                name: org.name,
                code: org.code,
                interval_percentages: org.interval_percentages,
                sales: org.interval_percentages?.sales?.amount || 0,
				invoices: org.interval_percentages?.invoices?.amount || 0,
				sales_percentage: org.interval_percentages?.sales?.percentage || 0,
				invoices_percentage: org.interval_percentages?.invoices?.percentage || 0,
                route: org.route,
                currency: org.currency_code,
            }));
    } else {
        return props.dashboard.table.sales
            .filter((org) => org.type !== "agent") 
            .map((org) => ({
                name: org.name,
                code: org.code,
                interval_percentages: org.interval_percentages,
                sales: org.interval_percentages?.sales?.amount || 0,
				invoices: org.interval_percentages?.invoices?.amount || 0,
				sales_percentage: org.interval_percentages?.sales?.percentage || 0,
				invoices_percentage: org.interval_percentages?.invoices?.percentage || 0,
                currency: org.currency_code,
            }));
    }
});

const toggleCurrency = () => {
	isOrganisation.value = !isOrganisation.value
}

const dashboardTable = [
	{
		tab_label: "Overview",
		tab_slug: "overview",
		type: "table",  // 
		data: tableDatas.value
	},
	{
		tab_label: "Profile",
		tab_slug: "profile",
		type: "xxx",  // 
		data: null
	},
]
</script>

<template>
	<div>
		<DashboardSettings
			v-if="props.dashboard?.settings"
			@toggle-currency="toggleCurrency"
			@update-checked="onToggleChecked"
			:intervalOptions="props.dashboard?.interval_options"
			:checked="checked"
			:tableType="tableType"
			:settings="props.dashboard?.settings" />

		<DashboardTable
			v-if="props.dashboard?.table"
			:dashboardTable
			:tableData="tableDatas"
			:locale="locale"
			:tableType="props.tableType"
			:totalAmount="props.dashboard.total"
			:selectedDateOption="props.dashboard.settings.selected_interval" />

		<DashboardWidget v-if="props.dashboard?.widgets" :widgetsData="dashboard.widgets" />
	</div>
</template>

<script setup lang="ts">
import DashboardSettings from "./DashboardSettings.vue"
import DashboardTable from "./DashboardTable.vue"
import DashboardWidget from "./DashboardWidget.vue"
import { inject, ref, computed, provide } from "vue"

const props = defineProps<{
	data: {
		interval_options: {
			label: string
			labelShort: string
			value: string
		}[]
		dashboard_stats: {
			columns?: {
				widgets?: {
					data: {
						currency: {
							code: string
							symbol: string
						}
						total: {
							[key: string]: {
								total_invoices: number
								total_refunds: number
								total_sales: string
							}
						}
						organisations: {
							name: string
							type: string
							code: string
							currency: {
								code: string
								symbol: string
							}
							invoices: {
								number_invoices: number
							}
							sales: {
								number_sales: number
							}
							refunds: {
								number_refunds: number
							}

							// number_invoices_type_refund?: number
							// number_invoices?: number
							// number_invoices_type_invoice?: number
							interval_percentages?: {
								sales?: {
									[key: string]: {
										amount: string
										percentage: number
										difference: number
									}
								}
								invoices?: {
									[key: string]: {
										amount: string
										percentage: number
										difference: number
									}
								}
								refunds?: {
									[key: string]: {
										amount: string
										percentage: number
										difference: number
									}
								}
							}
						}[]
					}[]
				}[]
			}[]
			settings: {
				selected_interval?: string  // 'ytd' | 'mtd' | 'wtd'
			}
		}
	}

	dashboard: {}
	intervalOptions: {}[]
}>()

// const selectedOption = props.dashboard?.settings?.selected_interval || ref('all')
provide('dsbr-selectedInterval', props.dashboard?.settings?.selected_interval)


const organisationSymbols = computed(() => {
	const symbols = props.groupStats.organisations
		.filter((org) => org.type !== "agent")
		.map((org) => org.currency.symbol)
		.filter(Boolean)
	return [...new Set(symbols)].join(" / ")
})

// const currency = ref([
// 	{
// 		name: "Group",
// 		code: "grp",
// 		symbol: props.data?.dashboard_stats?.columns?.[0]?.widgets?.[0]?.data?.currency?.symbol,
// 	},
// 	{ name: "Organisation", code: "org", symbol: null },
// ])

// const selectedCurrency = ref(currency.value[0])
// const isOrganisation = ref(selectedCurrency.value.code === "org")
// const toggleCurrency = () => {
// 	selectedCurrency.value = isOrganisation.value ? currency.value[1] : currency.value[0]
// }

// const abcdef = computed(() => {
// 	return props.data?.dashboard_stats?.columns?.[0]?.widgets?.[0]?.data?.organisations
// 		.filter((org) => org.type !== "agent")
// 		.map((org) => {
// 			return {
// 				name: org.name,
// 				code: org.code,
// 				interval_percentages: org.interval_percentages,
// 				sales: org.sales || 0,
// 				currency:
// 					selectedCurrency.value.code === "grp"
// 						? props.data.dashboard_stats.columns[0].widgets[0].data.currency.code
// 						: org.currency.code,
// 			}
// 		})
// })
// console.log(abcdef.value, "haha")
</script>


<template>
	<div>
		<DashboardSettings
			:groupCurrencySymbol="groupCurrencySymbol"
			:intervalOptions
			:settings="props.dashboard?.settings"
		/>
		<!-- <DashboardTable /> -->
		<DashboardWidget :widgetsData="dashboard.widgets" />
	</div>
</template>


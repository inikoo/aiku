<script setup lang="ts">
import { inject, ref } from "vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { Switch } from "@headlessui/vue"
import { useLocaleStore } from "@/Stores/locale"
import { RadioGroup, RadioGroupOption } from "@headlessui/vue"
import { Pie } from "vue-chartjs"
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from "chart.js"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Row from "primevue/row"
import ColumnGroup from "primevue/columngroup"
import { computed } from "vue"
import { useTruncate } from "@/Composables/useTruncate"
import ToggleSwitch from "primevue/toggleswitch"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronDown } from "@far"
import { faChartLine, faPlay, faSortDown, faSortUp } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { Head } from "@inertiajs/vue3"
import { trans } from "laravel-vue-i18n"
import { get } from "lodash"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import { useGetCurrencySymbol } from "@/Composables/useCurrency"
import Tag from "@/Components/Tag.vue"
import { faCog, faFolderOpen, faSeedling, faTimesCircle, faTriangle } from "@fal"
import { faArrowDown, faArrowUp } from "@fad"
import Select from "primevue/select"
import tippy from "tippy.js"
import "tippy.js/dist/tippy.css"
import DashboardCard from "@/Components/DataDisplay/InfoDashboardCard.vue"
import shinyButton from "@/Components/ShinyButton.vue"
import axios from "axios"

import Tabs from "primevue/tabs"
import TabList from "primevue/tablist"
import Tab from "primevue/tab"
import TabPanels from "primevue/tabpanels"
import TabPanel from "primevue/tabpanel"
import Dashboard from "@/Components/DataDisplay/Dashboard/Dashboard.vue"
import { capitalize } from "@/Composables/capitalize"

library.add(faTriangle, faChevronDown, faSeedling, faTimesCircle, faFolderOpen, faPlay, faCog, faChartLine)

const props = defineProps<{
	title: string
	groupStats: {
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
	}
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
		}
		widgets: {
			column_count: number
			components: {
				type: string
				col_span?: number
				row_span?: number
				data: {}
			}
		}
		settings: {}
	}
}>()

// console.log("groupStats total: ", props.groupStats.total)
// console.log("groupStats Organisations: ", props.groupStats)
console.log("interval_options: ", props.interval_options)
const layout = inject("layout", layoutStructure)
const locale = inject("locale", aikuLocaleStructure)
console.log(props.dashboard_stats.columns?.widgets, "columns")

// Decriptor: Date interval
const selectedDateOption = ref<string>(props.dashboard_stats.settings.selected_interval || "ytd")

// const currencyValue = ref('group')

ChartJS.register(ArcElement, Tooltip, Legend, Colors)
const options = {
	responsive: true,
	plugins: {
		legend: {
			display: false,
		},
		tooltip: {
			titleFont: {
				size: 10,
				weight: "lighter",
			},
			bodyFont: {
				size: 11,
				weight: "bold",
			},
		},
	},
}


const currency = ref([
	{
		name: "Group",
		code: "grp",
		symbol: props.dashboard_stats?.columns?.[0]?.widgets?.[0]?.data?.currency?.symbol,
	},
	{ name: "Organisation", code: "org", symbol: null },
])


const selectedCurrency = ref(currency.value[0])
const isOrganisation = ref(selectedCurrency.value.code === "org")
const toggleCurrency = () => {
	selectedCurrency.value = isOrganisation.value ? currency.value[1] : currency.value[0]
}
const isNegative = (value: number): boolean => value < 0

// const updateRouteAndUser = async (interval: string) => {
// 	selectedDateOption.value = interval

// 	try {
// 		const response = await axios.patch(route("grp.models.user.update", layout.user.id), {
// 			settings: {
// 				selected_interval: interval,
// 			},
// 		})
// 		console.log("Update successful:", response.data)
// 	} catch (error) {
// 		console.error("Error updating user:", error.response?.data || error.message)
// 	}
// }

console.log(layout.user.id, "layoutt")
</script>

<template>
	<Head :title="capitalize(title)" />

	<div class="grid grid-cols-12 m-3 gap-4">
		<div class="col-span-12">
			<Dashboard 
				:dashboard="dashboard_stats"
			/>
		</div>
	</div>
</template>

<style>
.align-right {
	justify-items: end;
	text-align: right;
}
.transition-opacity {
	transition: opacity 0.3s ease-in-out;
}
.overflow-x-auto {
	overflow-x: auto;
}
</style>

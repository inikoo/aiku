<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 18 Jan 2024 15:36:09 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

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

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronDown } from "@far"
import { faTriangle } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { Head } from "@inertiajs/vue3"
import { trans } from "laravel-vue-i18n"
import { get } from "lodash"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import { useGetCurrencySymbol } from "@/Composables/useCurrency"
import Tag from "@/Components/Tag.vue"

library.add(faTriangle, faChevronDown)

const props = defineProps<{
	dashboard: any
	interval_options: {
		label: string
		labelShort: string
		value: string
	}[]
}>()

console.log(props.dashboard, "hehe")
const selectedDateOption = ref<string>("all")
const locale = inject("locale", aikuLocaleStructure)

const datas = computed(() => {
	return props.dashboard.shops.map((org) => {
		return {
			name: org.name,
			code: org.code,
			interval_percentages: org.interval_percentages,
			// refunds: org.refunds.number_refunds || 0,
			// refunds_diff: 0,
			// invoices: org.invoices.number_invoices || 0,
			// invoices_diff: get(org, ['sales', `invoices_${selectedDateOption.value}`], 0),
			sales: org.sales || 0,
			// sales_diff: get(org, ['sales', `org_amount_${selectedDateOption.value}`], 0),
		}
	})
})
const selectedTabGraph = ref(0)
console.log(datas)
</script>

<template>
	<Head :title="trans('Dashboard')" />
	<div class="px-4 sm:px-6 lg:px-8">
		<!-- <pre>{{ props.groupStats.organisations }}</pre> -->
		<div class="mt-4 block">
			<nav class="isolate flex rounded border-b border-gray-300" aria-label="Tabs">
				<div
					v-for="(interval, idxInterval) in datas"
					:key="idxInterval"
					@click="() => (selectedDateOption = interval.code)"
					:class="[
						interval.code === selectedDateOption
							? ''
							: 'text-gray-500 hover:text-gray-700',
					]"
					class="relative min-w-0 flex-1 overflow-hidden bg-white hover:bg-gray-100 py-0 text-center text-sm cursor-pointer select-none focus:z-10">
					<span>{{ interval.code }}</span>
					<span
						aria-hidden="true"
						:class="[
							interval.code === selectedDateOption
								? 'bg-indigo-500'
								: 'bg-transparent',
							'absolute inset-x-0 bottom-0 h-0.5',
						]" />
				</div>
			</nav>
		</div>

		<div class="mt-6">
			<!-- Card Grid -->
			<div class="grid grid-cols-1 md:grid-cols-5 gap-6">
				<!-- Card 1 -->
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Sitting Time</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>

				<!-- Card 2 -->
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Dispatch Time</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-4 block">
			<nav class="isolate flex rounded border-b border-gray-300" aria-label="Tabs">
				<div
					v-for="(interval, idxInterval) in datas"
					:key="idxInterval"
					@click="() => (selectedDateOption = interval.code)"
					:class="[
						interval.code === selectedDateOption
							? ''
							: 'text-gray-500 hover:text-gray-700',
					]"
					class="relative min-w-0 flex-1 overflow-hidden bg-white hover:bg-gray-100 py-0 text-center text-sm cursor-pointer select-none focus:z-10">
					<span>{{ interval.code }}</span>
					<span
						aria-hidden="true"
						:class="[
							interval.code === selectedDateOption
								? 'bg-indigo-500'
								: 'bg-transparent',
							'absolute inset-x-0 bottom-0 h-0.5',
						]" />
				</div>
			</nav>
		</div>

		<div class="mt-6">
			<!-- Card Grid -->
			<div class="grid grid-cols-1 md:grid-cols-5 gap-6">
				<!-- Card 1 -->
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">In Basket</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>

				<!-- Card 2 -->
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Submitted</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>

				<!-- Card 3 -->
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Picking</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Invoicing</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Today</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-red-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-times-circle"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">Total Customer</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-between text-gray-500 text-sm">
						<div class="flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder-open"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">
							Active Customers
						</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-center text-gray-500 text-sm">
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
				<div class="bg-white border rounded-lg shadow-md p-4">
					<div class="flex items-center">
						<div class="flex-1 text-gray-600 font-semibold text-sm">
							Customer With Orders
						</div>
					</div>
					<div class="mt-4">
						<div class="text-green-500 text-3xl font-bold flex items-center">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-folder"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
					<div class="mt-4 flex justify-center text-gray-500 text-sm">
						<div class="flex items-center text-green-500">
							<FontAwesomeIcon
								fixed-width
								icon="fal fa-seedling"
								class="text-gray-400 mr-4 mt-1"
								aria-hidden="true" />
							0
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Section: Date options -->
		<div class="mt-4 block">
			<nav class="isolate flex rounded border-b border-gray-300" aria-label="Tabs">
				<div
					v-for="(interval, idxInterval) in interval_options"
					:key="idxInterval"
					@click="() => (selectedDateOption = interval.value)"
					:class="[
						interval.value === selectedDateOption
							? ''
							: 'text-gray-500 hover:text-gray-700',
					]"
					class="relative min-w-0 flex-1 overflow-hidden bg-white hover:bg-gray-100 py-0 text-center text-sm cursor-pointer select-none focus:z-10">
					<span>{{ interval.value }}</span>
					<span
						aria-hidden="true"
						:class="[
							interval.value === selectedDateOption
								? 'bg-indigo-500'
								: 'bg-transparent',
							'absolute inset-x-0 bottom-0 h-0.5',
						]" />
				</div>
			</nav>
		</div>

		<div class="mt-6">
			<DataTable
				:value="datas"
				stripedRows
				showGridlines
				removableSort
				tableStyle="min-width: 50rem">
				<Column field="name" sortable class="overflow-hidden transition-all" header="Name">
					<template #body="{ data }">
						<div class="relative">
							<Transition name="spin-to-down" mode="out-in">
								<div :key="data.name">
									{{ data.name }}
								</div>
							</Transition>
						</div>
					</template>
				</Column>

				<!-- Sales -->
				<Column
					field="sales"
					sortable
					class="overflow-hidden transition-all"
					header="Sales"
					headerStyle="text-align: green; width: 250px">
					<template #body="{ data }">
						<div class="flex justify-end relative">
							<Transition name="spin-to-down" mode="out-in">
								<div :key="data.sales?.[`sales_${selectedDateOption}`]">
									{{
										useLocaleStore().currencyFormat(
											dashboard.currency.code,
											data.sales?.[`sales_${selectedDateOption}`] || 0
										)
									}}
								</div>
							</Transition>
						</div>
					</template>
				</Column>

				<!-- Sales: Diff 1y -->
				<Column
					field="sales_diff"
					sortable
					class="overflow-hidden transition-all"
					header="&Delta; 1y"
					headerStyle="text-align: green; width: 270px">
					<template #body="{ data }">
						<div class="flex justify-end relative">
							<!-- {{ `${data.interval_percentages?.sales?.[selectedDateOption]?.difference}_${data.interval_percentages?.sales?.[selectedDateOption]?.percentage}` }} -->
							<Transition name="spin-to-down" mode="out-in">
								<div
									:key="`${data.interval_percentages?.sales[selectedDateOption].difference}_${data.interval_percentages?.sales[selectedDateOption].percentage}`">
									{{
										selectedDateOption !== "all"
											? useLocaleStore().currencyFormat(
													dashboard.currency.code,
													data.interval_percentages?.sales[
														selectedDateOption
													].difference || 0
											  )
											: useLocaleStore().currencyFormat(
													dashboard.currency.code,
													data.interval_percentages?.sales[
														selectedDateOption
													].amount || 0
											  )
									}}

									{{
										data.interval_percentages?.sales[selectedDateOption]
											?.percentage
											? `(${(
													data.interval_percentages.sales[
														selectedDateOption
													].percentage * 100
											  ).toFixed(2)}%)`
											: ""
									}}
									<FontAwesomeIcon
										v-if="
											data.interval_percentages?.sales[selectedDateOption]
												?.percentage
										"
										:icon="
											data.interval_percentages.sales[selectedDateOption]
												.percentage < 0
												? 'fad fa-arrow-down'
												: 'fad fa-arrow-up'
										"
										:class="
											data.interval_percentages.sales[selectedDateOption]
												.percentage < 0
												? 'text-red-500'
												: 'text-green-500'
										" />
									<!-- {{ data.interval_percentages?.sales[selectedDateOption] }} -->
								</div>
							</Transition>
						</div>
					</template>
				</Column>

				<!-- Total -->
				<ColumnGroup type="footer">
					<Row>
						<Column footer="Total"> Total </Column>

						<Column
							:footer="
								useLocaleStore().currencyFormat(
									dashboard.currency.code,
									Number(dashboard.total[selectedDateOption].total_sales)
								)
							"
							footerStyle="text-align:right" />
						<Column footer="" footerStyle="text-align:right" />
					</Row>
				</ColumnGroup>
			</DataTable>
		</div>

		<!-- <pre>{{ groupStats }}</pre> -->
	</div>
</template>

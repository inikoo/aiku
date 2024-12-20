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
import { faSortDown, faSortUp, faTriangle } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { Head } from "@inertiajs/vue3"
import { trans } from "laravel-vue-i18n"
import { get } from "lodash"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import { useGetCurrencySymbol } from "@/Composables/useCurrency"
import Tag from "@/Components/Tag.vue"
import { faFolderOpen, faSeedling, faTimesCircle } from "@fal"
import { faArrowDown, faArrowUp } from "@fad"

library.add(
	faTriangle,
	faChevronDown,
	faSeedling,
	faTimesCircle,
	faFolderOpen,
	faSortUp,
	faSortDown
)

const props = defineProps<{
	groupStats: {
		currency: {
			code: string
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
}>()

console.log("groupStats total: ", props.groupStats.total)
console.log("groupStats Organisations: ", props.groupStats)
console.log("interval_options: ", props.interval_options)
const layout = inject("layout", layoutStructure)
const locale = inject("locale", aikuLocaleStructure)

// Decriptor: Date interval
const selectedDateOption = ref<string>("ytd")

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

const abcdef = computed(() => {
	return props.groupStats.organisations
		.filter((org) => org.type != "agent")
		.map((org) => {
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

const shop = ref()
</script>

<template>
	<Head :title="trans('Dashboard')" />
	<div class="grid grid-cols-12 m-3 gap-4">
		<!-- <pre>{{ props.groupStats.organisations }}</pre> -->

		<!-- Section: Date options -->
		<div class="col-span-12 space-y-4">
		<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
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
					:value="abcdef"
					removableSort
					tableStyle="min-width: 50rem">
					<Column
						field="code"
						sortable
						class="overflow-hidden transition-all"
						header="Code">
						<template #body="{ data }">
							<div class="relative">
								<Transition name="spin-to-down" mode="out-in">
									<div :key="data.code">
										{{ data.code }}
									</div>
								</Transition>
							</div>
						</template>
					</Column>


					<!-- Refunds -->
					<Column
						field="refunds"
						sortable
					class="header-right"
						header="Refunds"
						header-style="text-align: right; width: 200px"
						header-class="bg-red-500 text-right">
						
						<template #body="{ data }">
							<div class="flex justify-end relative">
								<Transition name="spin-to-down" mode="out-in">
									<div
										:key="
											data.interval_percentages?.refunds[selectedDateOption]
												?.amount || 0
										">
										{{
											locale.number(
												data.interval_percentages?.refunds[
													selectedDateOption
												]?.amount || 0
											)
										}}
									</div>
								</Transition>
							</div>
						</template>
					</Column>

					<!-- Refunds: Diff 1y -->
					<Column
						field="refunds_diff"
						sortable
						class="overflow-hidden transition-all"
						header="&Delta; 1y"
						
						header-style="text-align: right width: 200px">
						<template #body="{ data }">
							<div class="flex justify-end relative">
								<!-- {{ `${data.interval_percentages?.refunds?.[selectedDateOption]?.difference}_${data.interval_percentages?.refunds?.[selectedDateOption]?.percentage}` }} -->
								<Transition name="spin-to-down" mode="out-in">
									<div
										:key="`${data.interval_percentages?.refunds[selectedDateOption].difference}_${data.interval_percentages?.refunds[selectedDateOption].percentage}`"
										style="
											display: flex;
											align-items: center;
											line-height: 1;
											gap: 6px;
										">
										<span
											style="
												font-size: 16px;
												font-weight: 500;
												line-height: 1;
											">
											{{
												data.interval_percentages?.refunds[
													selectedDateOption
												]?.percentage
													? `${
															data.interval_percentages.refunds[
																selectedDateOption
															].percentage > 0
																? "+"
																: ""
													  }${data.interval_percentages.refunds[
															selectedDateOption
													  ].percentage.toFixed(2)}%`
													: `0%`
											}}
										</span>
										<FontAwesomeIcon
											v-if="
												data.interval_percentages?.refunds[
													selectedDateOption
												]?.percentage
											"
											:icon="
												data.interval_percentages.refunds[
													selectedDateOption
												].percentage < 0
													? 'fas fa-sort-down'
													: 'fas fa-sort-up'
											"
											style="font-size: 20px; margin-top: 6px"
											:class="
												data.interval_percentages.refunds[
													selectedDateOption
												].percentage < 0
													? 'text-red-500'
													: 'text-green-500'
											" />
									</div>
								</Transition>
							</div>
						</template>
					</Column>

					<!-- Invoice -->
					<Column
						field="invoices"
						sortable
						class="overflow-hidden transition-all"
						header="Invoices"
						headerStyle="text-align: right; width: 200px;">
						<template #body="{ data }">
							<div class="flex justify-end relative">
								<Transition name="spin-to-down" mode="out-in">
									<div
										:key="
											data.interval_percentages?.invoices[selectedDateOption]
												?.amount || 0
										">
										{{
											locale.number(
												data.interval_percentages?.invoices[
													selectedDateOption
												]?.amount || 0
											)
										}}
									</div>
								</Transition>
							</div>
						</template>
					</Column>

					<!-- Invoice: Diff 1y -->
					<Column
						field="invoices_diff"
						sortable
						class="overflow-hidden transition-all"
						header="&Delta; 1y"
						headerStyle="text-align: green; width: 200px">
						<template #body="{ data }">
							<div class="flex justify-end relative">
								<Transition name="spin-to-down" mode="out-in">
									<div class="flex justify-end relative">
										<!-- {{ `${data.interval_percentages?.invoices?.[selectedDateOption]?.difference}_${data.interval_percentages?.invoices?.[selectedDateOption]?.percentage}` }} -->
										<Transition name="spin-to-down" mode="out-in">
											<div
												:key="`${data.interval_percentages?.invoices[selectedDateOption].difference}_${data.interval_percentages?.invoices[selectedDateOption].percentage}`"
												style="
													display: flex;
													align-items: center;
													line-height: 1;
													gap: 6px;
												">
												<span
													style="
														font-size: 16px;
														font-weight: 500;
														line-height: 1;
													">
													{{
														data.interval_percentages?.invoices[
															selectedDateOption
														]?.percentage
															? `${
																	data.interval_percentages
																		.invoices[
																		selectedDateOption
																	].percentage > 0
																		? "+"
																		: ""
															  }${data.interval_percentages.invoices[
																	selectedDateOption
															  ].percentage.toFixed(2)}%`
															: `0%`
													}}
												</span>
												<FontAwesomeIcon
													v-if="
														data.interval_percentages?.invoices[
															selectedDateOption
														]?.percentage
													"
													:icon="
														data.interval_percentages.invoices[
															selectedDateOption
														].percentage < 0
															? 'fas fa-sort-down'
															: 'fas fa-sort-up'
													"
													style="font-size: 20px; margin-top: 6px"
													:class="
														data.interval_percentages.invoices[
															selectedDateOption
														].percentage < 0
															? 'text-red-500'
															: 'text-green-500'
													" />
											</div>
										</Transition>
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
									<div
										:key="
											data.interval_percentages?.sales[selectedDateOption]
												?.amount
										">
										{{
											useLocaleStore().numberShort(
												groupStats.currency.code,
												data.interval_percentages?.sales[selectedDateOption]
													?.amount || 0
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
										:key="`${data.interval_percentages?.sales[selectedDateOption].difference}_${data.interval_percentages?.sales[selectedDateOption].percentage}`"
										style="
											display: flex;
											align-items: center;
											line-height: 1;
											gap: 6px;
										">
										<span
											style="
												font-size: 16px;
												font-weight: 500;
												line-height: 1;
											">
											{{
												data.interval_percentages?.sales[selectedDateOption]
													?.percentage
													? `${
															data.interval_percentages.sales[
																selectedDateOption
															].percentage > 0
																? "+"
																: ""
													  }${data.interval_percentages.sales[
															selectedDateOption
													  ].percentage.toFixed(2)}%`
													: `0%`
											}}
										</span>
										<FontAwesomeIcon
											v-if="
												data.interval_percentages?.sales[selectedDateOption]
													?.percentage
											"
											:icon="
												data.interval_percentages.sales[selectedDateOption]
													.percentage < 0
													? 'fas fa-sort-down'
													: 'fas fa-sort-up'
											"
											style="font-size: 20px; margin-top: 6px"
											:class="
												data.interval_percentages.sales[selectedDateOption]
													.percentage < 0
													? 'text-red-500'
													: 'text-green-500'
											" />
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
									groupStats.total[selectedDateOption].total_refunds.toString()
								"
								footerStyle="text-align:right" />
							<Column footer="" footerStyle="text-align:right" />

							<Column
								:footer="
									groupStats.total[selectedDateOption].total_invoices.toString()
								"
								footerStyle="text-align:right" />
							<Column footer="" footerStyle="text-align:right" />

							<Column
								:footer="
									useLocaleStore().numberShort(
										groupStats.currency.code,
										Number(groupStats.total[selectedDateOption].total_sales)
									)
								"
								footerStyle="text-align:right" />
							<Column footer="" footerStyle="text-align:right" />
						</Row>
					</ColumnGroup>
				</DataTable>
			</div>
		</div>
		
		</div>

		<div
			v-if="
				groupStats.organisations
					.filter((org) => org.type !== 'agent')
					.map(
						(org) =>
							org.interval_percentages?.sales?.[selectedDateOption.value]?.amount || 0
					)
					.some((i) => {
						return !!Number(i)
					})
			"
			class="mt-10 w-1/2 flex flex-wrap gap-y-4 gap-x-4">
			<div class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
				<div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans("Refunds") }}</div>
				<div class="w-24">
					<Pie
						:data="{
							labels: groupStats.organisations
								.filter((org) => org.type !== 'agent')
								.map((org) => org.name),
							datasets: [
								{
									data: groupStats.organisations
										.filter((org) => org.type !== 'agent')
										.map(
											(org) =>
												org.interval_percentages?.sales?.[
													selectedDateOption.value
												]?.amount || 0
										),
									hoverOffset: 4,
								},
							],
						}"
						:options="options" />
				</div>
				<!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `sales_org_currency_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
			</div>

			<div
				v-if="
					groupStats.organisations
						.filter((org) => org.type !== 'agent')
						.map((org) => get(org, ['sales', `sales_org_currency_all`], 0))
						.some((i) => {
							return !!Number(i)
						})
				"
				class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
				<div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans("Invoices") }}</div>
				<div class="w-24">
					<Pie
						:data="{
							labels: groupStats.organisations
								.filter((org) => org.type !== 'agent')
								.map((org) => org.name),
							datasets: [
								{
									data: groupStats.organisations
										.filter((org) => org.type !== 'agent')
										.map((org) =>
											get(org, ['sales', `sales_org_currency_all`], 0)
										),
									hoverOffset: 4,
								},
							],
						}"
						:options="options" />
				</div>
				<!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `sales_org_currency_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
			</div>

			<div
				v-if="
					groupStats.organisations
						.filter((org) => org.type !== 'agent')
						.map((org) => get(org, ['sales', `sales_org_currency_all`], 0))
						.some((i) => {
							return !!Number(i)
						})
				"
				class="py-5 px-5 flex gap-x-6 bg-gray-50 rounded-md border border-gray-300 w-fit">
				<div class="w-fit font-semibold py-1 mb-1 text-center">{{ trans("Sales") }}</div>
				<div class="w-24">
					<Pie
						:data="{
							labels: groupStats.organisations
								.filter((org) => org.type !== 'agent')
								.map((org) => org.name),
							datasets: [
								{
									data: groupStats.organisations
										.filter((org) => org.type !== 'agent')
										.map((org) =>
											get(org, ['sales', `sales_org_currency_all`], 0)
										),
									hoverOffset: 4,
								},
							],
						}"
						:options="options" />
				</div>
				<!-- <div class="flex flex-col justify-between ">
                    <template v-for="org in groupStats.organisations">
                        <div v-if="org.type !== 'agent'" class="space-x-2">
                            <span class="text-lg">{{ org.code }}:</span>
                            <span class="text-gray-500">{{ useLocaleStore().currencyFormat(currencyValue === 'organisation' ? org.currency.code : groupStats.currency.code, get(org, ['sales', `sales_org_currency_all`], 0)) }}</span>
                        </div>
                    </template>
                </div> -->
			</div>
		</div>

		<!-- <pre>{{ groupStats }}</pre> -->
	</div>
</template>

<style scoped lang="scss">
.header-right{
	.p-datatable-column-header-content {
    justify-content: end !important;
}

}
</style>
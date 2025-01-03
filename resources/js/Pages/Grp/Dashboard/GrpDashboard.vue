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
import { faPlay, faSortDown, faSortUp } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { Head } from "@inertiajs/vue3"
import { trans } from "laravel-vue-i18n"
import { get } from "lodash"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import { useGetCurrencySymbol } from "@/Composables/useCurrency"
import Tag from "@/Components/Tag.vue"
import { faFolderOpen, faSeedling, faTimesCircle, faTriangle } from "@fal"
import { faArrowDown, faArrowUp } from "@fad"
import Select from "primevue/select"
import tippy from "tippy.js"
import "tippy.js/dist/tippy.css"
import DashboardCard from "@/Components/DataDisplay/InfoDashboardCard.vue"
import shinyButton from "@/Components/ShinyButton.vue"
import axios from 'axios'

library.add(faTriangle, faChevronDown, faSeedling, faTimesCircle, faFolderOpen, faPlay)

const props = defineProps<{
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
		settings: {
			
		}
	}
}>()

console.log("groupStats total: ", props.groupStats.total)
console.log("groupStats Organisations: ", props.groupStats)
console.log("interval_options: ", props.interval_options)
const layout = inject("layout", layoutStructure)
const locale = inject("locale", aikuLocaleStructure)

// Decriptor: Date interval
const selectedDateOption = ref<string>(props.dashboard_stats.settings.selected_interval || 'ytd')

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
		.filter((org) => org.type !== "agent")
		.map((org) => {
			return {
				name: org.name,
				code: org.code,
				interval_percentages: org.interval_percentages,
				sales: org.sales || 0,
				currency:
					selectedCurrency.value.code === "grp"
						? props.groupStats.currency.code
						: org.currency.code,
			}
		})
})

const currency = ref([
	{ name: "Group", code: "grp", symbol: props.groupStats.currency.symbol },
	{ name: "Organisation", code: "org", symbol: null },
])

const organisationSymbols = computed(() => {
	const symbols = props.groupStats.organisations
		.filter((org) => org.type !== "agent") 
		.map((org) => org.currency.symbol) 
		.filter(Boolean)
	return [...new Set(symbols)].join(" / ") 
})

const selectedCurrency = ref(currency.value[0])
const isOrganisation = ref(selectedCurrency.value.code === "org")
const toggleCurrency = () => {
	isOrganisation.value = !isOrganisation.value
	selectedCurrency.value = isOrganisation.value ? currency.value[1] : currency.value[0]
}
const isNegative = (value: number): boolean => value < 0

const updateRouteAndUser = async (interval: string) => {
    selectedDateOption.value = interval;

    try {
        const response = await axios.patch(route('grp.models.user.update', layout.user.id), {
          
                settings: {
                    selected_interval: interval,
                },
       
        });
        console.log('Update successful:', response.data); 
    } catch (error) {
        console.error('Error updating user:', error.response?.data || error.message);
    }
};

console.log( layout.user.id,'layoutt');

</script>

<template>
	<Head :title="trans('Dashboard')" />
	<div class="grid grid-cols-12 m-3 gap-4">
		<!-- <pre>{{ props.groupStats.organisations }}</pre> -->
		<!-- Section: Date options -->
		<div class="col-span-12 space-y-4">
			<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
				<div class="flex justify-end items-center space-x-4">
					<div class="flex items-center space-x-4">
						<!-- Group Label -->
						<p
							class="font-medium transition-opacity"
							:class="{ 'opacity-60': isOrganisation }">
							{{ props.groupStats.currency.symbol }} 
						</p>

						<!-- PrimeVue Toggle Switch -->
						<ToggleSwitch
							v-model="isOrganisation"
							class="mx-2"
							@change="toggleCurrency" />

						<!-- Organisation Label -->
						<p
							class="font-medium transition-opacity"
							:class="{ 'opacity-60': !isOrganisation }">
							{{ organisationSymbols }}
						</p>
					</div>
				</div>

				<div class="mt-4 block">
					<nav class="isolate flex rounded border-b border-gray-300" aria-label="Tabs">
						<div
							v-for="(interval, idxInterval) in interval_options"
							:key="idxInterval"
							@click="updateRouteAndUser(interval.value)"
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
					<DataTable :value="abcdef" removableSort>
						<Column sortable>
							<template #header>
								<div class="flex items-center justify-between">
									<span class="font-bold">Code</span>
								</div>
							</template>
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
						<Column sortable headerClass="align-right" hidden>
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-bold">Refunds</span>
								</div>
							</template>
							<template #body="{ data }">
								<div class="flex justify-end relative">
									<Transition name="spin-to-down" mode="out-in">
										<div
											:key="
												data.interval_percentages?.refunds[
													selectedDateOption
												]?.amount || 0
											">
											<span>
												{{
													locale.number(
														data.interval_percentages?.refunds[
															selectedDateOption
														]?.amount || 0
													)
												}}
											</span>
										</div>
									</Transition>
								</div>
							</template>
						</Column>

						<!-- Refunds: Diff 1y -->
						<Column
							hidden
							sortable
							class="overflow-hidden transition-all"
							headerClass="align-right"
							headerStyle="text-align: green; width: 270px">
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-semibold">
										<FontAwesomeIcon
											fixed-width
											icon="fal fa-triangle"
											aria-hidden="true" />
										1y
									</span>
								</div>
							</template>
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
														: `0.0%`
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
							sortable
							class="overflow-hidden transition-all"
							headerClass="align-right">
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-bold">Invoices</span>
								</div>
							</template>
							<template #body="{ data }">
								<div class="flex justify-end relative">
									<Transition name="spin-to-down" mode="out-in">
										<div
											v-tooltip="
												useLocaleStore().numberShort(
													data.currency,
													data.interval_percentages?.invoices[
														selectedDateOption
													]?.amount || 0
												)
											"
											:key="
												data.interval_percentages?.invoices[
													selectedDateOption
												]?.amount || 0
											">
											{{
												useLocaleStore().numberShort(
													data.currency,
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
							headerClass="align-right"
							headerStyle="width: 200px">
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-bold">
										<FontAwesomeIcon
											fixed-width
											icon="fal fa-triangle"
											aria-hidden="true" />
										1y</span
									>
								</div>
							</template>
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
																: `0.0%`
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
							headerClass="align-right"
							headerStyle="text-align: green; width: 250px">
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-bold">Sales</span>
								</div>
							</template>
							<template #body="{ data }">
								<div class="flex justify-end relative">
									<Transition name="spin-to-down" mode="out-in">
										<div
											v-tooltip="
												useLocaleStore().currencyFormat(
													data.currency,
													data.interval_percentages?.sales[
														selectedDateOption
													]?.amount || 0
												)
											"
											:key="
												data.interval_percentages?.sales[selectedDateOption]
													?.amount
											">
											{{
												useLocaleStore().numberShort(
													data.currency,
													data.interval_percentages?.sales[
														selectedDateOption
													]?.amount || 0
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
							headerClass="align-right"
							headerStyle="text-align: green; width: 270px">
							<template #header>
								<div class="flex justify-end items-end">
									<span class="font-bold text-gray-700">
										<FontAwesomeIcon
											fixed-width
											icon="fal fa-triangle"
											aria-hidden="true" />
										1y</span
									>
								</div>
							</template>
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
													data.interval_percentages?.sales[
														selectedDateOption
													]?.percentage
														? `${
																data.interval_percentages.sales[
																	selectedDateOption
																].percentage > 0
																	? "+"
																	: ""
														  }${data.interval_percentages.sales[
																selectedDateOption
														  ].percentage.toFixed(2)}%`
														: `0.0%`
												}}
											</span>
											<FontAwesomeIcon
												v-if="
													data.interval_percentages?.sales[
														selectedDateOption
													]?.percentage
												"
												:icon="
													data.interval_percentages.sales[
														selectedDateOption
													].percentage < 0
														? 'fas fa-play'
														: 'fas fa-play'
												"
												style="font-size: 20px; margin-top: 6px"
												:class="
													data.interval_percentages.sales[
														selectedDateOption
													].percentage < 0
														? 'text-red-500 rotate-90'
														: 'text-green-500 rotate-[-90deg]'
												" />
											<div v-else style="width: 20px; height: 20px"></div>
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
									hidden
									:footer="
										groupStats.total[
											selectedDateOption
										].total_refunds.toString()
									"
									footerStyle="text-align:right" />
								<Column hidden footer="" footerStyle="text-align:right" />

								<Column
									:footer="
										useLocaleStore().numberShort(
											groupStats.currency.code,
											Number(
												groupStats.total[
													selectedDateOption
												].total_invoices.toString()
											)
										)
									"
									footerStyle="text-align:right" />
								<Column footer="" footerStyle="text-align:right" />

								<Column
									v-tooltip="
										useLocaleStore().currencyFormat(
											groupStats.currency.code,
											Number(groupStats.total[selectedDateOption].total_sales)
										)
									"
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
		<div class="col-span-12">
			<div class="flex flex-row flex-wrap gap-4">
				<!-- Changed to flex-row for horizontal layout -->
				<DashboardCard
					v-for="(org, index) in props.groupStats.organisations.filter(
						(org) => org.type !== 'agent'
					)"
					:key="index"
					:value="
						useLocaleStore().currencyFormat(
							groupStats.currency.code,
							org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0
						)
					"
					:description="`Sales for ${org.name}`"
					:showRedBorder="
						isNegative(
							org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0
						)
					"
					:showIcon="
						isNegative(
							org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0
						)
					" />
			</div>
		</div>

		<!-- <pre>{{ groupStats }}</pre> -->
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
</style>

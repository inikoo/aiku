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
import ToggleSwitch from "primevue/toggleswitch"

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
const selectedDateOption = ref<string>("ytd")
const locale = inject("locale", aikuLocaleStructure)
const checked = ref(true);

const datas = computed(() => {
  return props.dashboard.shops
    .filter((org) => {
      // Filter based on checkbox state
      if (checked.value) {
        return org.state !== "closed"; // Exclude closed shops when checked is true
      }
      return true; // Include all shops when checked is false
    })
    .map((org) => ({
      name: org.name,
      code: org.code,
      interval_percentages: org.interval_percentages,
      sales: org.sales || 0,
    }));
});
const selectedTabGraph = ref(0)

</script>

<template>
	<Head :title="trans('Dashboard')" />
	<div class="grid grid-cols-12 m-3 gap-4">
		<!-- <pre>{{ props.groupStats.organisations }}</pre> -->
		<div class="col-span-12 space-y-4">
			<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
				<div class="flex justify-end top-4 right-4">
					<ToggleSwitch v-model="checked" />
				</div>
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
					<DataTable :value="datas" removableSort tableStyle="min-width: 50rem">
						<Column
							field="name"
							sortable
							class="overflow-hidden transition-all"
							header="Name">
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

						<!-- Refunds -->
						<Column
							field="refunds"
							sortable
							class="overflow-hidden transition-all"
							header="Refunds"
							header-style="text-align: green; width: 250px"
							headerClass="bg-red-500">
							<template #body="{ data }">
								<div class="flex justify-end relative">
									<Transition name="spin-to-down" mode="out-in">
										<div
											:key="
												data.interval_percentages?.refunds[
													selectedDateOption
												]?.amount || 0
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
							headerStyle="text-align: green; width: 130px">
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
												data.interval_percentages?.invoices[
													selectedDateOption
												]?.amount || 0
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
													dashboard.currency.code,
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
														: `0%`
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
														? 'fas fa-sort-down'
														: 'fas fa-sort-up'
												"
												style="font-size: 20px; margin-top: 6px"
												:class="
													data.interval_percentages.sales[
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

						<!-- Total -->
						<ColumnGroup type="footer">
							<Row>
								<Column footer="Total"> Total </Column>
								<Column
									:footer="
										dashboard.total[selectedDateOption].total_refunds.toString()
									"
									footerStyle="text-align:right" />
								<Column footer="" footerStyle="text-align:right" />

								<Column
									:footer="
										dashboard.total[
											selectedDateOption
										].total_invoices.toString()
									"
									footerStyle="text-align:right" />
								<Column footer="" footerStyle="text-align:right" />
								<Column
									:footer="
										useLocaleStore().numberShort(
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
			</div>
		</div>
		<!-- <pre>{{ groupStats }}</pre> -->
	</div>
</template>

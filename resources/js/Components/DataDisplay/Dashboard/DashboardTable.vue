<script setup lang="ts">
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Row from "primevue/row"
import ColumnGroup from "primevue/columngroup"
import { ref, computed } from "vue"
import { useLocaleStore } from "@/Stores/locale"

const props = defineProps<{
	tableData: {}
	groupStats: Object
	locale: any
	selectedDateOption: String
}>()


</script>
<template>
	<div class="bg-white mb-2 text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
		<div class="">
		
			<DataTable :value="tableData" removableSort>
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
										data.interval_percentages?.refunds[selectedDateOption]
											?.amount || 0
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
									<span style="font-size: 16px; font-weight: 500; line-height: 1">
										{{
											data.interval_percentages?.refunds[selectedDateOption]
												?.percentage
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
											data.interval_percentages?.refunds[selectedDateOption]
												?.percentage
										"
										:icon="
											data.interval_percentages.refunds[selectedDateOption]
												.percentage < 0
												? 'fas fa-sort-down'
												: 'fas fa-sort-up'
										"
										style="font-size: 20px; margin-top: 6px"
										:class="
											data.interval_percentages.refunds[selectedDateOption]
												.percentage < 0
												? 'text-red-500'
												: 'text-green-500'
										" />
								</div>
							</Transition>
						</div>
					</template>
				</Column>

				<!-- Invoice -->
				<Column sortable class="overflow-hidden transition-all" headerClass="align-right">
					<template #header>
						<div class="flex justify-end items-end">
							<span class="font-bold">Invoices</span>
						</div>
					</template>
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
											data.interval_percentages?.invoices[selectedDateOption]
												?.amount || 0
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
																data.interval_percentages.invoices[
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
														? 'fas fa-play'
														: 'fas fa-play'
												"
												style="font-size: 20px; margin-top: 6px"
												:class="
													data.interval_percentages.invoices[
														selectedDateOption
													].percentage < 0
														? 'text-red-500 rotate-90'
														: 'text-green-500 rotate-[-90deg]'
												" />
											<div v-else style="width: 20px; height: 20px"></div>
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
											data.interval_percentages?.sales[selectedDateOption]
												?.amount || 0
										)
									"
									:key="
										data.interval_percentages?.sales[selectedDateOption]?.amount
									">
									{{
										useLocaleStore().CurrencyShort(
											data.currency,
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
									<span style="font-size: 16px; font-weight: 500; line-height: 1">
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
												: `0.0%`
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
												? 'fas fa-play'
												: 'fas fa-play'
										"
										style="font-size: 20px; margin-top: 6px"
										:class="
											data.interval_percentages.sales[selectedDateOption]
												.percentage < 0
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
							:footer="groupStats.total[selectedDateOption].total_refunds.toString()"
							footerStyle="text-align:right" />
						<Column hidden footer="" footerStyle="text-align:right" />

						<Column
							:footer="
								locale.number(
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
								useLocaleStore().CurrencyShort(
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
</template>
<style scoped></style>

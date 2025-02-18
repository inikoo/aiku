<script setup lang="ts">
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Row from "primevue/row"
import ColumnGroup from "primevue/columngroup"
import { ref, computed } from "vue"
import { useLocaleStore } from "@/Stores/locale"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Tabs from "primevue/tabs"
import TabList from "primevue/tablist"
import Tab from "primevue/tab"
import { Link } from "@inertiajs/vue3"
import { router } from "@inertiajs/vue3"

const props = defineProps<{
	tableData: {}[]
	locale: any
	totalAmount: {
		total_invoices: number
		total_sales: number
		total_refunds: number
	}
	selectedDateOption: String
	tableType?: string
	current?: string
	settings: {}
	dashboardTable: {
		tab_label: string
		tab_slug: string
		type: string // 'table'
		data: {}
	}[]
}>()

function ShopDashboard(shop: any) {
	return route(shop?.route?.name, shop?.route?.parameters)
}

function ShopInvoiceDashboard(shop: any) {	
	return route(shop?.route_invoice?.name, shop?.route_invoice?.parameters)
}

function ShopRefundDashboard(shop: any) {
	return route(shop?.route_refund?.name, shop?.route_invoice?.parameters)
}

const activeIndexTab = ref(props.current)

const selectedTab = computed(() => {
	return props.dashboardTable.find((tab) => tab.tab_slug === activeIndexTab.value)
})
function useTabChangeDashboard(tab_slug: string) {
	if (tab_slug === activeIndexTab.value) {
		return
	}

	router.reload({
		data: { tab_dashboard_interval: tab_slug },
		// only: ['dashboard_stats'],
		onSuccess: () => {
			activeIndexTab.value = tab_slug
		},
		onError: (error) => {
			console.error("Error reloading dashboard:", error)
		},
	})
}
</script>

<template>
	<div class="bg-white mb-2 text-gray-800 rounded-lg p-4 shadow-md border border-gray-200">
		<div class="">
			<Tabs :value="activeIndexTab">
				<TabList>
					<Tab
						v-for="tab in dashboardTable"
						@click="() => useTabChangeDashboard(tab.tab_slug)"
						:key="tab.tab_slug"
						:value="tab.tab_slug">
						<FontAwesomeIcon
							:icon="tab.tab_icon"
							class=""
							fixed-width
							aria-hidden="true" />
						{{ tab.tab_label }}</Tab
					>
				</TabList>
			</Tabs>

			<DataTable v-if="selectedTab.type === 'table'" :value="selectedTab.data" removableSort>
				<Column sortable field="code">
					<template #header>
						<div class="flex items-center justify-between">
							<span class="font-bold">Code</span>
						</div>
					</template>
					<template #body="{ data }" v-if="tableType == 'org' || current == 'shops'">
						<div class="relative">
							<Transition name="spin-to-down" mode="out-in">
								<div :key="data.name">
									<Link :href="ShopDashboard(data)" class="hover-underline">
										{{ data.name }}
									</Link>
								</div>
							</Transition>
						</div>
					</template>
					<template #body="{ data }" v-else>
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
				field="interval_percentages.refunds.amount"
				sortField="interval_percentages.refunds.amount"
				sortable 
				headerClass="align-right">
					<template #header>
						<div class="flex justify-end items-end">
							<span class="font-bold">Refunds</span>
						</div>
					</template>
					<template #body="{ data }">
						<div class="flex justify-end relative">
							<Transition name="spin-to-down" mode="out-in">
                                <div :key="data?.refunds || 0">
									<Link :href="ShopRefundDashboard(data)" class="hover-underline">
										{{
											locale.number(
												data?.interval_percentages?.refunds?.amount || 0
											)
										}}
									</Link>
								</div>
								<!-- <div :key="data.interval_percentages?.refunds?.amount || 0">
									<span>
										{{
											locale.number(
												data.interval_percentages?.refunds?.amount || 0
											)
										}}
									</span>
								</div> -->
							</Transition>
						</div>
					</template>
				</Column>
				<!-- Refunds: Diff 1y -->
				<Column
					sortable
					class="overflow-hidden transition-all"
					headerClass="align-right"
					headerStyle=" width: 130px">
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
									:key="`${data.interval_percentages?.refunds?.difference}_${data.interval_percentages?.refunds?.percentage}`"
									style=" align-items: center">
									<span style="font-size: 16px; font-weight: 500">
										{{
											data.interval_percentages?.refunds?.percentage
												? `${
														data.interval_percentages?.refunds
															?.percentage > 0
															? "+"
															: ""
												  }${data.interval_percentages?.refunds?.percentage.toFixed(
														2
												  )}%`
												: `0.0%`
										}}
									</span>
									<FontAwesomeIcon
										v-if="
											data.interval_percentages?.invoices?.percentage
										"
										:icon="
											data.interval_percentages.invoices?.percentage <
											0
												? 'fas fa-play'
												: 'fas fa-play'
										"
										style="font-size: 16px"
										:class="
											data.interval_percentages.invoices?.percentage <
											0
												? 'text-[#ff6347] rotate-90'
												: 'text-[#26a65b] rotate-[-90deg]'
										" />
									<div v-else style="width: 60px"></div>
								</div>
							</Transition>
						</div>
					</template>
				</Column>
				<!-- Invoice -->
				<Column
					sortable
					field="interval_percentages.invoices.amount"
					sortField="interval_percentages.invoices.amount"
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
								<div :key="data?.invoices || 0">
									<Link :href="ShopInvoiceDashboard(data)" class="hover-underline" >
										{{
											locale.number(
												data?.interval_percentages?.invoices?.amount || 0
											)
										}}
									</Link>
								</div>
							</Transition>
						</div>
					</template>
				</Column>
				<!-- Invoice: Diff 1y -->
				<Column
					field="interval_percentages.invoices.percentage"
					sortField="interval_percentages.invoices.percentage"
					sortable
					class="overflow-hidden transition-all"
					headerClass="align-right"
					headerStyle=" width: 130px">
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
									<!-- {{ `${data.interval_percentages?.invoices?.?.difference}_${data.interval_percentages?.invoices?.?.percentage}` }} -->
									<Transition name="spin-to-down" mode="out-in">
										<div
											:key="`${data.interval_percentages?.invoices?.difference}_${data.interval_percentages?.invoices?.percentage}`"
											style="align-items: center">
											<span style="font-size: 16px; font-weight: 500" class="pr-1">
												{{
													data.interval_percentages?.invoices?.percentage
														? `${
																data.interval_percentages.invoices
																	?.percentage > 0
																	? "+"
																	: ""
														  }${data.interval_percentages.invoices?.percentage.toFixed(
																2
														  )}%`
														: `0.0%`
												}}
											</span>
											<FontAwesomeIcon
												v-if="
													data.interval_percentages?.invoices?.percentage
												"
												:icon="
													data.interval_percentages.invoices?.percentage <
													0
														? 'fas fa-play'
														: 'fas fa-play'
												"
												style="font-size: 16px"
												:class="
													data.interval_percentages.invoices?.percentage <
													0
														? 'text-[#ff6347] rotate-90'
														: 'text-[#26a65b] rotate-[-90deg]'
												" />
											<div v-else style="width: 60px"></div>
										</div>
									</Transition>
								</div>
							</Transition>
						</div>
					</template>
				</Column>
				<!-- Sales -->
				<Column
					field="interval_percentages.sales.amount"
					sortField="interval_percentages.sales.amount"
					sortable
					class="overflow-hidden transition-all"
					headerClass="align-right"
					>
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
											data.currency_code,
											data.interval_percentages?.sales?.amount || 0
										)
									"
									:key="data.interval_percentages?.sales?.amount">
									{{
										useLocaleStore().CurrencyShort(
											data.currency_code,
											data.interval_percentages?.sales?.amount || 0,
											props.settings.selected_amount
										)
									}}
								</div>
							</Transition>
						</div>
					</template>
				</Column>
				<!-- Sales: Diff 1y -->
				<Column
					field="interval_percentages.sales.percentage"
					sortField="interval_percentages.sales.percentage"
					sortable
					class="overflow-hidden transition-all"
					headerClass="align-right"
					headerStyle=" width: 130px">
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
							<!-- {{ `${data.interval_percentages?.sales?.?.difference}_${data.interval_percentages?.sales?.?.percentage}` }} -->
							<Transition name="spin-to-down" mode="out-in">
								<div
									:key="`${data.interval_percentages?.sales?.difference}_${data.interval_percentages?.sales?.percentage}`"
									style="align-items: center">
									<span style="font-size: 16px; font-weight: 500" class="pr-1">
										{{
											data.interval_percentages?.sales?.percentage
												? `${
														data.interval_percentages.sales.percentage >
														0
															? "+"
															: ""
												  }${data.interval_percentages?.sales?.percentage.toFixed(
														2
												  )}%`
												: `0.0%`
										}}
									</span>
									<FontAwesomeIcon
										v-if="data.interval_percentages?.sales?.percentage"
										:icon="
											data.interval_percentages.sales?.percentage < 0
												? 'fas fa-play'
												: 'fas fa-play'
										"
										style="font-size: 16px"
										:class="
											data.interval_percentages.sales?.percentage < 0
												? 'text-red-500 rotate-90'
												: 'text-green-500 rotate-[-90deg]'
										" />
									<div v-else style="width: 60px"></div>
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
							:footer="totalAmount.total_refunds.toString()"
							footerStyle="text-align:right" />
						<Column footerStyle="text-align:right" >
							<template #footer>
								<span style="font-size: 16px; font-weight: 500" class="pr-1">
									{{
										totalAmount.total_refunds_percentages
											? `${
													totalAmount.total_refunds_percentages > 0
														? "+"
														: ""
											  }${totalAmount.total_refunds_percentages.toFixed(2)}%`
											: "0.0%"
									}}
								</span>
								<FontAwesomeIcon
									v-if="totalAmount.total_refunds_percentages"
									:icon="
										totalAmount.total_refunds_percentages < 0
											? 'fas fa-play'
											: 'fas fa-play'
									"
									style="font-size: 16px"
									:class="
										totalAmount.total_refunds_percentages < 0
											? 'text-red-500 rotate-90'
											: 'text-green-500 rotate-[-90deg]'
									" />
								<div v-else style="width: 60px"></div>
							</template>
						</Column>
						<Column
							:footer="locale.number(Number(totalAmount.total_invoices.toString()))"
							footerStyle="text-align:right" />
						<Column footerStyle="text-align:right">
							<template #footer>
								<span style="font-size: 16px; font-weight: 500" class="pr-1">
									{{
										totalAmount.total_invoices_percentages
											? `${
													totalAmount.total_invoices_percentages > 0
														? "+"
														: ""
											  }${totalAmount.total_invoices_percentages.toFixed(2)}%`
											: "0.0%"
									}}
								</span>
								<FontAwesomeIcon
									v-if="totalAmount.total_invoices_percentages"
									:icon="
										totalAmount.total_invoices_percentages < 0
											? 'fas fa-play'
											: 'fas fa-play'
									"
									style="font-size: 16px"
									:class="
										totalAmount.total_invoices_percentages < 0
											? 'text-red-500 rotate-90'
											: 'text-green-500 rotate-[-90deg]'
									" />
								<div v-else style="width: 60px"></div>
							</template>
						</Column>
						<Column
							v-tooltip="
								useLocaleStore().currencyFormat(
									'GBP',
									Number(totalAmount.total_sales)
								)
							"
							:footer="
								useLocaleStore().CurrencyShort(
									'GBP',
									Number(totalAmount.total_sales),
									props.settings.selected_amount
								)
							"
							footerStyle="text-align:right" />
						<Column footerStyle="text-align:right ">
							<template #footer>
								<span style="font-size: 16px; font-weight: 500" class="pr-1">
									{{
										totalAmount.total_sales_percentages
											? `${
													totalAmount.total_sales_percentages > 0
														? "+"
														: ""
											  }${totalAmount.total_sales_percentages.toFixed(2)}%`
											: "0.0%"
									}}
								</span>
								<FontAwesomeIcon
									v-if="totalAmount.total_sales_percentages"
									:icon="
										totalAmount.total_sales_percentages < 0
											? 'fas fa-play'
											: 'fas fa-play'
									"
									style="font-size: 16px"
									:class="
										totalAmount.total_sales_percentages < 0
											? 'text-red-500 rotate-90'
											: 'text-green-500 rotate-[-90deg]'
									" />
								<div v-else style="width: 60px"></div>
							</template>
						</Column>
					</Row>
				</ColumnGroup>
			</DataTable>

			<div v-else>Type not found</div>
		</div>
	</div>
</template>
<style scoped>
.hover-underline:hover {
  text-decoration: underline;
}

</style>
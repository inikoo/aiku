<script setup lang="ts">
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from "chart.js"
import { Pie } from "vue-chartjs"
import { trans } from "laravel-vue-i18n"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faUsers, faUserCheck, faUserSlash, faUserPlus, faMoneyBillWave } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { useLocaleStore } from "@/Stores/locale"
import { capitalize } from "@/Composables/capitalize"
import { ref, onMounted, onUnmounted } from "vue"

library.add(faUsers, faUserCheck, faUserSlash, faUserPlus, faMoneyBillWave)

ChartJS.register(ArcElement, Tooltip, Legend, Colors)

const locale = useLocaleStore()

// Dummy data to simulate customer-related backend data
const customerStats = ref({
	totalCustomers: {
		label: "Total Customers",
		count: 1200,
		cases: [],
	},
	activeCustomers: {
		label: "Active Customers",
		count: 950,
		cases: [
			{
				value: "weeklyActive",
				count: 700,
				label: "Weekly Active",
				icon: {
					icon: "fa-user-check",
					tooltip: "Weekly Active Customers",
					class: "text-green-500",
				},
			},
			{
				value: "monthlyActive",
				count: 850,
				label: "Monthly Active",
				icon: {
					icon: "fa-user-check",
					tooltip: "Monthly Active Customers",
					class: "text-yellow-500",
				},
			},
		],
	},
	inactiveCustomers: {
		label: "Inactive Customers",
		count: 250,
		cases: [],
	},
	newCustomers: {
		label: "New Customers",
		count: 150,
		cases: [
			{
				value: "thisMonth",
				count: 100,
				label: "This Month",
				icon: {
					icon: "fa-user-plus",
					tooltip: "New Customers This Month",
					class: "text-blue-500",
				},
			},
			{
				value: "lastMonth",
				count: 50,
				label: "Last Month",
				icon: {
					icon: "fa-user-plus",
					tooltip: "New Customers Last Month",
					class: "text-gray-500",
				},
			},
		],
	},
	customerRevenue: {
		label: "Revenue",
		count: 50000,
		cases: [
			{
				value: "highTier",
				count: 30000,
				label: "High Tier Customers",
				icon: {
					icon: "fa-money-bill-wave",
					tooltip: "Revenue from High-Tier Customers",
					class: "text-purple-500",
				},
			},
			{
				value: "lowTier",
				count: 20000,
				label: "Low Tier Customers",
				icon: {
					icon: "fa-money-bill-wave",
					tooltip: "Revenue from Low-Tier Customers",
					class: "text-pink-500",
				},
			},
		],
	},
})

// Chart options
const options = {
	responsive: true,
	plugins: {
		legend: { display: false },
		tooltip: {
			titleFont: { size: 10, weight: "lighter" },
			bodyFont: { size: 11, weight: "bold" },
		},
	},
}

// Listener for backend updates
onMounted(() => {
	window.Echo.private("customer.general").listen(".customers.dashboard", (e) => {
		if (e.data.counts) {
			Object.keys(e.data.counts).forEach((key) => {
				if (customerStats.value[key]) {
					customerStats.value[key].count = e.data.counts[key]
				}
			})
		}
		;["active", "new", "revenue"].forEach((status) => {
			if (e.data[status]) {
				Object.keys(e.data[status]).forEach((key) => {
					const targetCase = customerStats.value[status]?.cases.find(
						(item) => item.value === key
					)
					if (targetCase) targetCase.count = e.data[status][key]
				})
			}
		})
	})
})

onUnmounted(() => {
	window.Echo.private("customer.general").stopListening(".customers.dashboard")
})
</script>

<template>
	<div class="px-6">
		<dl class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-x-2 gap-y-3">
			<div
				v-for="(customerState, key) in customerStats"
				:key="key"
				class="px-4 py-5 sm:p-6 rounded-lg bg-white shadow tabular-nums">
				<dt class="text-base font-medium text-gray-400 capitalize">
					{{ customerState.label }}
				</dt>
				<dd class="mt-2 flex justify-between gap-x-2">
					<div
						class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
						<!-- Total Count -->
						<div class="flex gap-x-2 items-end">
							{{ locale.number(customerState.count) }}
							<span class="text-sm font-medium leading-4 text-gray-500">
								{{ trans("in total") }}
							</span>
						</div>

						<!-- Case Breakdown -->
						<div
							class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">
							<div
								v-for="dCase in customerState.cases"
								:key="dCase.value"
								class="flex gap-x-0.5 items-center font-normal"
								v-tooltip="capitalize(dCase.icon.tooltip)">
								<FontAwesomeIcon
									:icon="dCase.icon.icon"
									:class="dCase.icon.class"
									fixed-width
									:title="dCase.icon.tooltip"
									aria-hidden="true" />
								<span class="font-semibold">{{ locale.number(dCase.count) }}</span>
							</div>
						</div>
					</div>

					<!-- Pie Chart -->
					<div class="w-20">
						<Pie
							:data="{
								labels: customerState.cases.map((c) => c.label),
								datasets: [
									{
										data: customerState.cases.map((c) => c.count),
										hoverOffset: 4,
									},
								],
							}"
							:options="options" />
					</div>
				</dd>
			</div>
		</dl>
	</div>
</template>

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationCircle } from "@fortawesome/free-solid-svg-icons"
import { capitalize } from "@/Composables/capitalize"
import { inject } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"

const props = defineProps<{
	title: string
	pageHead: any
	data: {
		data: Array<{
			name: string
			number: number
			route: any
		}>
	}
}>()

const locale = inject("locale", aikuLocaleStructure)

library.add(faExclamationCircle)
</script>

<template>
	<!-- Page Header -->
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />

	<!-- Dashboard Grid -->
	<div class="grid grid-cols-12 m-3 gap-4">
		<!-- Left Column -->
		<div class="col-span-3 space-y-4">
			<!-- Predicted Months DataTable -->
			<div class="bg-white p-4 rounded-lg shadow-md">
				<DataTable :value="data.data" class="text-gray-900">
					<Column field="name" >
						<template #body="slotProps">
								{{ slotProps.data.name }}		
						</template>
					</Column>
					<Column field="number" style="text-align: right;">
						<template #body="slotProps">
              <Link
								:href="slotProps.data.route"
								class="primaryLink">
							{{ locale.number(slotProps.data.number) }}
            </Link>
						</template>
					</Column>
				</DataTable>
			</div>
		</div>

		<!-- Middle Column -->
		<div class="col-span-3 space-y-4">
			<!-- Stock Check Card -->
			<div class="bg-white p-4 rounded-lg shadow-md">
				<h3 class="font-bold mb-4">Stock check</h3>
				<div class="flex items-center justify-between">
					<div class="text-4xl font-bold">42</div>
					<span class="text-red-600 flex items-center">
						<i class="fas fa-exclamation-circle mr-2"></i> Days since last check
					</span>
				</div>
				<div class="mt-4">
					<p>Inventory accuracy</p>
					<div class="flex items-center mt-2">
						<div class="w-full bg-gray-200 rounded-full h-2">
							<div class="bg-green-500 h-2 rounded-full" style="width: 99.1%"></div>
						</div>
						<span class="ml-2 font-bold">99.1%</span>
					</div>
				</div>
			</div>

			<!-- Warehouse Card -->
			<div class="bg-white p-4 rounded-lg shadow-md">
				<h3 class="font-bold mb-4">Warehouse</h3>
				<div class="text-4xl font-bold">81%</div>
				<p class="text-sm mb-4">Utilization</p>
				<p class="text-2xl font-bold">$4.25M</p>
				<p>Value of stock</p>
			</div>
		</div>

		<!-- Right Column -->
		<div class="col-span-6 space-y-4">
			<!-- In Stock Table -->
			<!-- 			<div class="bg-white p-4 rounded-lg shadow-md">
				<h3 class="font-bold mb-4">In stock</h3>
				<DataTable :value="data.data" class="text-gray-900">
					<Column field="name" header="Name">
						<template #body="slotProps">
							<Link
								:href="slotProps.data.route"
								class="text-blue-600 hover:underline">
								{{ slotProps.data.name }}
							</Link>
						</template>
					</Column>
					<Column field="number" header="In stock">
						<template #body="slotProps">
							{{ locale.number(slotProps.data.number) }}
						</template>
					</Column>
				</DataTable>
			</div> -->

			<!-- Returns and Chart -->
			<div class="grid  gap-4">
				<!-- Returns Card -->
				<div class="bg-white p-4 rounded-lg shadow-md">
					<h3 class="font-bold mb-4">Returns and Return Rate</h3>
					<div class="grid grid-cols-2 gap-4 items-center">
						<!-- Returns Section -->
						<div>
							<div class="text-4xl font-bold">43</div>
							<p class="text-sm">To be processed</p>
							<p class="text-4xl font-bold mt-2">2.9%</p>
							<p class="text-sm">Return rate</p>
						</div>

						<!-- Chart Section -->
						<div>
							<h4 class="font-bold mb-2">Return rate by month</h4>
							<div class="h-24">
								<!-- Example line chart -->
								<svg viewBox="0 0 100 40" class="w-full h-full">
									<polyline
										fill="none"
										stroke="blue"
										stroke-width="2"
										points="0,30 20,20 40,25 60,15 80,20 100,10" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

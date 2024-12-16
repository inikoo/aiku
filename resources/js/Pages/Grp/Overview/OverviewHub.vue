<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationCircle } from "@fortawesome/free-solid-svg-icons"
import { capitalize } from "@/Composables/capitalize"
import { inject, ref, computed, onMounted, onUnmounted } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { faFilter, faInboxOut, faUser } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps<{
	title: string
	pageHead: any
	data: {
		data: Array<{
			name: string
			number: number
			route: any
			icon: string
		}>
	}
}>()

const locale = inject("locale", aikuLocaleStructure)

library.add(faExclamationCircle, faInboxOut, faUser, faFilter)

// Search functionality
const searchQuery = ref("")
const isFilterVisible = ref(false)

const handleClickOutside = (event: Event) => {
	const target = event.target as HTMLElement
	if (!target.closest(".search-container")) {
		isFilterVisible.value = false
	}
}

onMounted(() => {
	document.addEventListener("click", handleClickOutside)
})

onUnmounted(() => {
	document.removeEventListener("click", handleClickOutside)
})

// Filtered data
const filteredData = computed(() => {
	return props.data.data.filter((item) => {
		const query = searchQuery.value.toLowerCase()
		return (
			item.name.toLowerCase().includes(query) ||
			(item.icon && item.icon.toLowerCase().includes(query))
		)
	})
})
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
				<DataTable :value="filteredData" class="text-gray-900" :showHeaders="false">
					<template #header>
						<div class="flex items-center w-full mb-2">
							<!-- Expandable Icon and Input -->
							<div
								class="search-container transition-all duration-300 ease-in-out flex items-center px-2 w-full"
								:class="isFilterVisible ? 'max-w-full' : 'max-w-[2.5rem]'">
								<!-- Icon -->
								<FontAwesomeIcon
									v-show="!isFilterVisible"
									fixed-width
									icon="fal fa-filter"
									class="text-gray-500 cursor-pointer hover:text-gray-700"
									@click="isFilterVisible = true" />
								<!-- Input Field -->
								<div class="flex items-center w-full relative">
									<input
										v-show="isFilterVisible"
										type="text"
										v-model="searchQuery"
										class="border border-gray-300 rounded appearance-none block w-full px-3 py-2 text-sm leading-none transition-all duration-300 placeholder:text-gray-400 placeholder:italic focus:border-gray-500 focus:ring-0 focus:outline-none"
										placeholder="Search items..."
										autofocus />
									<!-- Clear Button -->
									<button
										v-show="isFilterVisible && searchQuery"
										@click="searchQuery = ''"
										class="absolute right-2 text-gray-500 hover:text-gray-700">
										✕
									</button>
								</div>
							</div>
						</div>
					</template>
					<template #empty> No Product found. </template>
					<Column field="icon">
						<template #body="slotProps">
							<FontAwesomeIcon
								fixed-width
								:icon="slotProps.data.icon"
								class="mr-4 text-gray-500"
								aria-hidden="true" />
						</template>
					</Column>

					<Column field="name">
						<template #body="slotProps">
							<span class="text-gray-800">{{ slotProps.data.name }}</span>
						</template>
					</Column>

					<Column field="number" style="text-align: right">
						<template #body="slotProps">
							<Link
								:href="slotProps.data.route"
								class="text-blue-500 hover:underline">
								{{ locale.number(slotProps.data.number) }}
							</Link>
						</template>
					</Column>
				</DataTable>
			</div>
		</div>

		<!-- Middle Column -->
		<div class="col-span-3 space-y-4">
			<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
				<h3 class="text-gray-500 font-semibold text-lg mb-4">The Nutrition Store</h3>
				<div class="relative bg-gray-100 border border-green-500 rounded-lg px-4 py-3 mb-6">
					<div>
						<p class="text-5xl font-bold leading-tight">275</p>
						<p class="text-gray-500 text-sm mt-1">Total orders today</p>
					</div>
					<!-- Green Icon in Bottom Right -->
					<div
						class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center shadow">
						✓
					</div>
				</div>
				<div>
					<p class="text-4xl font-bold leading-tight ">$6,058</p>
					<p class="text-gray-500 text-sm mt-1">Sales today</p>
				</div>
			</div>
		</div>

		<!-- Right Column -->
		<div class="col-span-3 space-y-4">
			<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
				<h3 class="text-gray-500 font-semibold text-lg mb-4">The Yoga Store</h3>
				<div class="relative px-4 py-3 mb-6" style="height: 102px">
					<!-- Adjusted height -->
					<div>
						<p class="text-5xl font-bold leading-tight">46</p>
						<p class="text-gray-500 text-sm mt-1">Ad spend this week</p>
					</div>
					<!-- Red Icon in Bottom Right -->
				</div>
				<div>
					<p class="text-4xl font-bold leading-tight ">$2,596</p>
					<p class="text-gray-500 text-sm mt-1">Sales today</p>
				</div>
			</div>
		</div>

		<!-- Added Beside Right -->
		<div class="col-span-3 space-y-4">
			<div class="flex flex-col gap-4 p-4">
				<!-- Card 1: Cart Abandonment Rate -->
				<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
					<p class="text-5xl font-bold leading-tight text-gray-700">
						45<span class="text-3xl">%</span>
					</p>
					<p class="text-gray-500 text-sm mt-2">Cart abandonment rate</p>
				</div>

				<!-- Card 2: Ad Spend This Week -->
				<div
					class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-red-400 relative">
					<p class="text-5xl font-bold leading-tight text-gray-700">$2,345</p>
					<p class="text-gray-500 text-sm mt-2">Ad spend this week</p>
					<!-- Red Exclamation Icon -->
					<div
						class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md">
						!
					</div>
				</div>

				<!-- Card 3: Total Newsletter Subscribers -->
				<div class="bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200">
					<p class="text-5xl font-bold leading-tight text-gray-700">
						55.7<span class="text-xl">K</span>
					</p>
					<p class="text-gray-500 text-base mt-2">Total newsletter subscribers</p>
					<!-- Progress Bar -->
					<div class="mt-4">
						<div class="w-full bg-gray-300 rounded-full h-1.5">
							<div class="bg-blue-500 h-1.5 rounded-full" style="width: 55%"></div>
						</div>
						<div class="flex justify-between text-xs text-gray-500 mt-1">
							<span>55%</span>
							<span>100%</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.search-container {
	transition: width 0.3s ease;
}
</style>

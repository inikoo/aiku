<script setup lang="ts">
import Modal from "@/Components/Utils/Modal.vue"
import { debounce } from "lodash"
import { ref } from "vue"

// Search functionality refs
const resultsSearch = ref()
const isLoadingSearch = ref(false)
const searchValue = ref("")

// Modal open state
const isOpen = defineModel<boolean>()

// Debounced API fetch function
const fetchApi = debounce(async (query: string) => {
	if (query.trim() !== "") {
		resultsSearch.value = null
		isLoadingSearch.value = true
		try {
			const response = await fetch(`http://app.aiku.test/ask-bot?q=${query}`)
			const data = await response.json()
			resultsSearch.value = data.data
			console.log("query:", query, resultsSearch.value)
		} catch (error) {
			console.error("Error fetching search results:", error)
		} finally {
			isLoadingSearch.value = false
		}
	}
}, 700)
</script>

<template>
	<Modal :isOpen="isOpen" @onClose="() => (isOpen = false)" width="w-3/4">
		<!-- Search Input -->
		<div class="relative">
			<input
				v-model="searchValue"
				@input="() => fetchApi(searchValue)"
				type="text"
				class="h-12 w-full border border-gray-300 bg-white rounded-lg pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring focus:ring-blue-300 focus:outline-none sm:text-sm"
				placeholder="Search..." />
			<!-- Search Icon -->
			<div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m-2.65 1.15a7.5 7.5 0 1110.35-10.35 7.5 7.5 0 01-10.35 10.35z" />
				</svg>
			</div>
		</div>

		<!-- Loading Indicator -->
		<div v-if="isLoadingSearch" class="mt-4 flex flex-col gap-4">
			<div v-for="n in 1" :key="n" class="h-10 skeleton w-full rounded-lg bg-gray-200 animate-pulse"></div>
		</div>

		<!-- Search Results -->
		<div v-else-if="resultsSearch?.response" class="mt-4">
			<div class="bg-white shadow-md rounded-lg p-4">
				<p class="text-gray-800 font-semibold">Results:</p>
				<div class="mt-2">
					<pre class="text-sm text-gray-600">{{ resultsSearch.response }}</pre>
				</div>
			</div>
		</div>

		<!-- No Results -->
		<div v-else-if="!isLoadingSearch && searchValue.trim() && !resultsSearch?.response" class="mt-4">
			<p class="text-center text-gray-500">No results found for "{{ searchValue }}"</p>
		</div>
	</Modal>
</template>


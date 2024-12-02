<script setup lang="ts">
import Modal from "@/Components/Utils/Modal.vue";
import { debounce } from "lodash";
import { ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faLampDesk } from "@fal";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(faLampDesk);

// Search functionality refs
const resultsSearch = ref();
const isLoadingSearch = ref(false);
const searchValue = ref("");
const errorSearch = ref(""); // Ref for error message
// Modal open state
const isOpen = defineModel<boolean>();

// Debounced API fetch function
const fetchApi = debounce(async (query: string) => {
    if (query.trim() !== "") {
        resultsSearch.value = null;
        isLoadingSearch.value = true;
        errorSearch.value = ""; // Reset error on new query
        try {
            const response = await fetch(`http://app.aiku.test/ask-bot?q=${query}`);
            
            if (!response.ok) {
                // Attempt to parse and display error message from response body
                const errorData = await response.json();
				
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            resultsSearch.value = data.data;
        } catch (error) {
            errorSearch.value = error.message || "An error occurred while fetching search results.";
        } finally {
            isLoadingSearch.value = false;
        }
    }
}, 700);

</script>

<template>
	<Modal :isOpen="isOpen" @onClose="() => (isOpen = false)" width="w-3/4" height="h-[80%]">
		<div class="relative">
			<input
				v-model="searchValue"
				@input="() => fetchApi(searchValue)"
				type="text"
				class="h-12 w-full border border-gray-300 bg-white rounded-lg pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring focus:ring-blue-300 focus:outline-none sm:text-sm"
				placeholder="Ask Anything..." />
			<div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
				<FontAwesomeIcon fixed-width icon="fal fa-lamp-desk" aria-hidden="true" />
			</div>
		</div>

		<div v-if="isLoadingSearch" class="mt-4 flex flex-col gap-4">
			<div v-for="n in 1" :key="n" class="h-10 skeleton w-full rounded-lg bg-gray-200 animate-pulse"></div>
		</div>

		<div v-else-if="resultsSearch?.response" class="mt-4">
			<div class="bg-white shadow-md rounded-lg p-4">
				<p class="text-gray-800 font-semibold">Results:</p>
				<div class="mt-2">
					<pre class="text-sm overflow-auto text-gray-600">{{ resultsSearch.response }}</pre>
				</div>
			</div>
		</div>

		<div v-else-if="!isLoadingSearch && errorSearch" class="mt-4">
			<p class="text-center text-red-500">{{ errorSearch }}</p>
		</div>

		<div v-else-if="!isLoadingSearch && searchValue.trim()" class="mt-4">
			<p class="text-center text-gray-500">No results found.</p>
		</div>
	</Modal>
</template>


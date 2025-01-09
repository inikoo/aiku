<script setup lang="ts">
import { inject, ref } from "vue"
import axios from "axios"

const selectedCurrency = ref(currency.value[0])
const selectedDateOption = ref<string>(props.dashboard_stats.settings.selected_interval || "ytd")
    const toggleCurrency = () => {
	selectedCurrency.value = isOrganisation.value ? currency.value[1] : currency.value[0]
}
const updateRouteAndUser = async (interval: string) => {
	selectedDateOption.value = interval

	try {
		const response = await axios.patch(route("grp.models.user.update", layout.user.id), {
			settings: {
				selected_interval: interval,
			},
		})
		console.log("Update successful:", response.data)
	} catch (error) {
		console.error("Error updating user:", error.response?.data || error.message)
	}
}
</script>
<template>
    <div>
        <div class="relative mt-6">
            <!-- Tabs in Card -->
            <div class="bg-white shadow-md rounded-lg border border-gray-300 p-4">
                <nav class="isolate flex rounded-full bg-white-50 border border-gray-200 p-1" aria-label="Tabs">
                    <div v-for="(interval, idxInterval) in interval_options" :key="idxInterval"
                        @click="updateRouteAndUser(interval.value)" :class="[
                            interval.value === selectedDateOption
                                ? 'bg-indigo-500 text-white font-medium'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
                        ]" class="relative flex-1 rounded-full py-2 px-4 text-center text-sm cursor-pointer select-none transition duration-200">
                        <span>{{ interval.value }}</span>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
<style scoped></style>
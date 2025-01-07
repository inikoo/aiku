<script setup lang="ts">
import { ref, computed } from "vue"

// Props for initializing accuracy
const props = defineProps({
	initialAccuracy: {
		type: Number,
		default: 50,
	},
})

// Accuracy state
const accuracy = ref(props.initialAccuracy)

// Computed needle rotation
const needleRotation = computed(() => {
	return -90 + accuracy.value * 1.8 // Rotate between -90 (0%) and 90 (100%)
})

function updateAccuracy(newAccuracy: number) {
	accuracy.value = Math.max(0, Math.min(newAccuracy, 100)) // Clamp between 0 and 100
}
</script>

<template>
	<div class="bg-indigo-900 text-white p-4 rounded-lg flex flex-col items-center w-48 shadow-lg">
		<h3 class="text-sm font-bold mb-2">Inventory accuracy</h3>

		<div class="relative w-32 h-16 overflow-hidden">
			<div
				class="absolute w-full h-full rounded-t-full bg-gradient-to-r from-gray-500 to-green-500 opacity-20"></div>

			<div
				class="absolute w-1 h-16 bg-red-500 origin-bottom transition-transform duration-300"
				:style="{ transform: `rotate(${needleRotation}deg)` }"></div>

			<div
				class="absolute w-4 h-4 bg-white rounded-full top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10"></div>
		</div>

		<!-- Percentage Display -->
		<div class="text-lg font-bold mt-4">{{ accuracy.toFixed(1) }}%</div>
	</div>
</template>

<style scoped>
/* Ensure smooth transitions and styles for the needle */
</style>

<script setup lang="ts">
import { defineProps, computed } from "vue";

const props = defineProps<{
	label: string;
	value: number | string;
	progressBar: {
		value: number; // Current progress value
		max: number;  // Maximum progress value
		color?: string; // Customizable color for the progress bar
	};
	customClasses?: string; // Additional classes for styling
}>();

// Computed properties for calculated percentage and color handling
const progressPercentage = computed(() => {
	return Math.min((props.progressBar.value / props.progressBar.max) * 100, 100); // Ensure it doesn't exceed 100%
});

const progressColor = computed(() => {
	return props.progressBar.color || "bg-blue-500"; // Default color if not provided
});
</script>

<template>
	<div
		:class="`bg-white text-gray-800 rounded-lg p-4 shadow-md border border-gray-200 max-w-sm ${
			customClasses || ''
		}`">
		<!-- Label Section -->
		<div class="flex justify-between items-center mb-3">
			<div>
				<h3 class="text-lg font-semibold">{{ label }}</h3>
				<span class="text-xs text-gray-500">{{ value }}</span>
			</div>
		</div>

		<!-- Progress Bar -->
		<div class="w-full">
			<div class="w-full bg-gray-300 rounded-full h-2">
				<div
					:class="`h-2 rounded-full ${progressColor}`"
					:style="{ width: `${progressPercentage}%` }"
				></div>
			</div>
			<div class="flex justify-between text-xs text-gray-500 mt-1">
				<span>{{ progressPercentage.toFixed(1) }}%</span>
				<span>{{ progressBar.max }}</span>
			</div>
		</div>
	</div>
</template>

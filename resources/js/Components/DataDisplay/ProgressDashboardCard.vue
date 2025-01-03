<!-- ProgressCard.vue -->
<script setup lang="ts">
import { defineProps } from "vue"

const props = defineProps<{
	label: string
	value: number | string
	progressBar: {
		value: number
		max: number
		color: string
	}
	customClasses?: string // Add custom classes for the card
}>()
</script>

<template>
	<div
		:class="`bg-white text-gray-800 rounded-lg p-6 shadow-md border border-gray-200 ${
			customClasses || ''
		}`">
		<h3 class="text-gray-500 font-semibold text-lg mb-4">{{ label }}</h3>
		<p class="text-4xl font-bold leading-tight">{{ value }}</p>
		<div class="mt-4">
			<div class="w-full bg-gray-300 rounded-full h-1.5">
				<div
					:class="`h-1.5 rounded-full bg-blue-500`"
					:style="{ width: `${progressBar.value}%` }"></div>
			</div>
			<div class="flex justify-between text-xs text-gray-500 mt-1">
				<span>{{ progressBar.value }}%</span>
				<span>{{ progressBar.max }}%</span>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { computed } from "vue"

// Props for dynamic behavior
defineProps({
	showRedBorder: {
		type: Boolean,
		default: undefined, // Allow parent to override or leave undefined
	},
	showIcon: {
		type: Boolean,
		default: false,
	},
	value: {
		type: String,
		required: true,
	},
	description: {
		type: String,
		required: true,
	},
})

// Dynamically determine if border should be red (if showRedBorder is undefined)
const isRedBorder = computed(() => {
	const numericValue = parseFloat(value.replace(/[^0-9.-]/g, "")) // Parse value as number
	return showRedBorder === undefined ? numericValue < 0 : showRedBorder
})
</script>

<template>
	<div
		:class="[
			'bg-white text-gray-800 rounded-lg p-6 shadow-md relative',
			isRedBorder ? 'border border-red-400' : 'border border-gray-200',
		]">
		<p class="text-4xl font-bold leading-tight text-gray-700">{{ value }}</p>
		<p class="text-gray-500 text-base mt-2">{{ description }}</p>
	->
		<div
			v-if="showIcon"
			class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md">
			!
		</div>
	</div>
</template>

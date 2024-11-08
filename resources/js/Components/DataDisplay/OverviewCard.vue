<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps<{
	icon: string
	label: string
	value: string
}>()
</script>

<template>
	<div
		class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
		<dt>
			<div class="absolute rounded-md bg-indigo-500 p-3">
				<FontAwesomeIcon
					:icon="icon"
					class="h-5 aspect-square text-white"
					fixed-width
					aria-hidden="true" />
			</div>
			<p class="ml-16 truncate text-sm font-medium text-gray-500">{{ label }}</p>
		</dt>
		<dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
			<p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
		</dd>
	</div>
</template>

<style scoped></style>

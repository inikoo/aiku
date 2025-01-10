<script setup lang="ts">
import ToggleSwitch from "primevue/toggleswitch"

const props = defineProps<{
	dashboard: any
	intervalOptions: any
	selectedDateOption: string
	isOrganisation: boolean
	organisationSymbols: string
}>()

const emit = defineEmits(["toggle-currency", "update-interval"])
</script>

<template>
	<div class="relative mb-2">
		<div class="flex justify-end items-center space-x-4">
			<p class="font-medium" :class="{ 'opacity-60': isOrganisation }">
				{{ dashboard.currency.symbol }}
			</p>

			<ToggleSwitch
				:modelValue="isOrganisation"
				class="mx-2"
				@change="$emit('toggle-currency')" />

			<p class="font-medium" :class="{ 'opacity-60': !isOrganisation }">
				{{ organisationSymbols }}
			</p>
		</div>

		<nav class="flex rounded-full bg-white-50 border border-gray-200 p-1">
			<div
				v-for="(interval, idx) in intervalOptions"
				:key="idx"
				@click="$emit('update-interval', interval.value)"
				:class="[
					interval.value === selectedDateOption
						? 'bg-indigo-500 text-white font-medium'
						: 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
				]"
				class="relative flex-1 rounded-full py-2 px-4 text-center text-sm cursor-pointer transition duration-200">
				<span>{{ interval.labelShort }}</span>
			</div>
		</nav>
	</div>
</template>

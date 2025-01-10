<script setup lang="ts">
import { inject, ref } from "vue"
import axios from "axios"

const props = defineProps<{
	groupCurrencySymbol: string
	intervalOptions: { label: string; value: string }[]
	selectedInterval: string
}>()

const emit = defineEmits(["update-interval"])
</script>

<template>
	<div class="settings-container">
		<!-- Currency Symbol -->
		<div class="flex items-center space-x-4">
			<p class="font-medium">{{ groupCurrencySymbol }}</p>
			<ToggleSwitch />
		</div>

		<!-- Interval Options -->
		<nav class="interval-options">
			<div
				v-for="(option, index) in intervalOptions"
				:key="index"
				:class="['option', { active: option.value === selectedInterval }]"
				@click="$emit('update-interval', option.value)">
				{{ option.label }}
			</div>
		</nav>
	</div>
</template>

<style>
.settings-container {
	margin-bottom: 20px;
}
.interval-options .option {
	padding: 8px 16px;
	cursor: pointer;
}
.interval-options .option.active {
	background-color: #007bff;
	color: #fff;
}
</style>

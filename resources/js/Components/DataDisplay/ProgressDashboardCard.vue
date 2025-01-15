<script setup lang="ts">
import { defineProps, computed } from "vue";

const props = defineProps<{
	progressBar: {
		value: number;
		max: number;
		color?: string;
	};
}>();

const progressPercentage = computed(() => {
	return Math.min((props.progressBar.value / props.progressBar.max) * 100, 100);
});

const progressColor = computed(() => {
	return props.progressBar.color || "bg-blue-500";
});
</script>

<template>
	<div>
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
</template>

<!--
  - Author Louis Perez
  - Created on 29-09-2026-14h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts" generic="T extends string">
import SelectButton from "primevue/selectbutton"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

defineProps<{
	options: Array<{ label: string; value: T; icon?: any; tone?: "gray" | "emerald" | "amber" | "red" | "primary"; disabled?: boolean }>
	ariaLabel?: string
	iconOnly?: boolean
}>()

const model = defineModel<T>()
</script>

<template>
	<SelectButton v-model="model" :options="options" optionLabel="label" optionValue="value" optionDisabled="disabled" dataKey="value" :allowEmpty="false" class="segmented-toggle" :aria-label="ariaLabel">
		<template #option="{ option }">
			<span v-tooltip="iconOnly ? option.label : undefined" :data-tone="option.tone" class="flex items-center gap-1.5">
				<FontAwesomeIcon v-if="option.icon" :icon="option.icon" fixed-width aria-hidden="true" />
				<span :class="{ 'sr-only': iconOnly }">{{ option.label }}</span>
			</span>
		</template>
	</SelectButton>
</template>

<style scoped>
.segmented-toggle {
	gap: 2px;
	padding: 2px;
	border-radius: 0.375rem;
	background-color: rgb(243 244 246);
}
.segmented-toggle :deep(.p-togglebutton) {
	padding: 0;
	border: 0;
	border-radius: 0.25rem;
	background: transparent;
	color: rgb(75 85 99);
	font-size: 0.8125rem;
	line-height: 1.25rem;
	transition: background-color 0.15s, color 0.15s;
}
.segmented-toggle :deep(.p-togglebutton-content) {
	padding: 0.25rem 0.75rem;
	border-radius: 0.25rem;
	background: transparent;
	box-shadow: none;
}
.segmented-toggle :deep(.p-togglebutton:not(.p-togglebutton-checked):hover) {
	background-color: rgb(229 231 235);
	color: rgb(17 24 39);
}
.segmented-toggle :deep(.p-togglebutton:not(.p-togglebutton-checked):active) {
	background-color: rgb(209 213 219);
}
.segmented-toggle :deep(.p-togglebutton-checked) {
	font-weight: 600;
}
.segmented-toggle :deep(.p-togglebutton-checked .p-togglebutton-content) {
	background-color: #fff;
	color: rgb(17 24 39);
	box-shadow: 0 1px 2px rgb(0 0 0 / 0.12), 0 0 0 1px rgb(209 213 219);
}
.segmented-toggle :deep(.p-togglebutton-checked .p-togglebutton-content svg) {
	color: rgb(22 163 74);
}
.segmented-toggle :deep(.p-togglebutton:focus-visible) {
	outline: 2px solid rgb(156 163 175);
	outline-offset: 1px;
}
.segmented-toggle :deep(.p-togglebutton-content:has([data-tone="gray"])) {
	--tone-text: rgb(107 114 128);
	--tone-bg: rgb(107 114 128);
	--tone-bg-strong: rgb(75 85 99);
	--tone-on: #fff;
}
.segmented-toggle :deep(.p-togglebutton-content:has([data-tone="emerald"])) {
	--tone-text: rgb(5 150 105);
	--tone-bg: rgb(16 185 129);
	--tone-bg-strong: rgb(5 150 105);
	--tone-on: #fff;
}
.segmented-toggle :deep(.p-togglebutton-content:has([data-tone="amber"])) {
	--tone-text: rgb(217 119 6);
	--tone-bg: rgb(251 191 36);
	--tone-bg-strong: rgb(245 158 11);
	--tone-on: rgb(69 26 3);
}
.segmented-toggle :deep(.p-togglebutton-content:has([data-tone="primary"])) {
	--tone-text: var(--app-accent);
	--tone-bg: var(--app-accent);
	--tone-bg-strong: var(--app-accent-strong);
	--tone-on: var(--app-accent-text);
}
.segmented-toggle :deep(.p-togglebutton-content:has([data-tone="red"])) {
	--tone-text: rgb(220 38 38);
	--tone-bg: rgb(220 38 38);
	--tone-bg-strong: rgb(185 28 28);
	--tone-on: #fff;
}
.segmented-toggle :deep(.p-togglebutton:not(.p-togglebutton-checked) .p-togglebutton-content:has([data-tone])) {
	color: var(--tone-text);
}
.segmented-toggle :deep(.p-togglebutton-checked .p-togglebutton-content:has([data-tone])) {
	background-color: var(--tone-bg);
	color: var(--tone-on);
}
.segmented-toggle :deep(.p-togglebutton-checked:hover .p-togglebutton-content:has([data-tone])) {
	background-color: var(--tone-bg-strong);
}
</style>

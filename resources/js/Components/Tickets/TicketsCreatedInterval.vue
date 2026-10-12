<script setup lang="ts">
import { computed, onMounted, ref } from "vue"
import { router } from "@inertiajs/vue3"
import SelectButton from "primevue/selectbutton"
import { ctrans } from "@/Composables/useTrans"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"

const props = withDefaults(
	defineProps<{
		options: Record<string, string>
		selected: string
		storageKey?: string
		label?: string
		param?: string
		compact?: boolean
		only?: string[]
	}>(),
	{ storageKey: "tickets-created-interval", label: "Created", param: "created", only: () => [] }
)

const storageKey = props.storageKey
const loadingInterval = ref<string | null>(null)

const intervalOptions = computed(() => Object.entries(props.options).map(([value, label]) => ({ value, label })))

const select = (interval: string) => {
	if (!interval || interval === props.selected) {
		return
	}
	try {
		localStorage.setItem(storageKey, interval)
	} catch {}
	router.reload({
		data: { [props.param]: interval, page: 1 },
		only: props.only,
		preserveScroll: true,
		onStart: () => (loadingInterval.value = interval),
		onFinish: () => (loadingInterval.value = null),
	})
}

onMounted(() => {
	if (new URLSearchParams(window.location.search).has(props.param)) {
		try {
			localStorage.setItem(storageKey, props.selected)
		} catch {}
		return
	}
	let remembered: string | null = null
	try {
		remembered = localStorage.getItem(storageKey)
	} catch {}
	if (remembered && remembered !== props.selected && remembered in props.options) {
		router.reload({ data: { [props.param]: remembered, page: 1 }, only: props.only, preserveScroll: true })
	}
})
</script>

<template>
	<div class="flex items-start gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5" :class="compact ? 'text-xs' : 'text-sm'">
		<span v-if="label" class="shrink-0 py-1 text-xs font-medium uppercase leading-5 tracking-wide text-gray-400">{{ ctrans(label) }}</span>
		<SelectButton
			:modelValue="selected"
			:options="intervalOptions"
			optionLabel="label"
			optionValue="value"
			dataKey="value"
			:allowEmpty="false"
			:disabled="loadingInterval !== null"
			class="interval-toggle"
			:class="{ 'interval-toggle-compact': compact }"
			:aria-label="label ? ctrans(label) : undefined"
			@update:modelValue="select">
			<template #option="{ option }">
				<span class="inline-flex items-center gap-1.5">
					<LoadingIcon v-if="loadingInterval === option.value" />
					{{ option.label }}
				</span>
			</template>
		</SelectButton>
	</div>
</template>

<style scoped>
.interval-toggle {
	display: flex;
	flex: 1 1 0;
	min-width: 0;
	flex-wrap: wrap;
	gap: 0.25rem;
}
.interval-toggle :deep(.p-togglebutton) {
	padding: 0;
	border: 0;
	border-radius: 0.375rem;
	background: transparent;
	color: rgb(75 85 99);
	font-size: inherit;
	line-height: 1.25rem;
	opacity: 1;
	transition: background-color 0.15s, color 0.15s;
}
.interval-toggle :deep(.p-togglebutton-content) {
	padding: 0.25rem 0.75rem;
	border-radius: 0.375rem;
	background: transparent;
	box-shadow: none;
}
.interval-toggle-compact :deep(.p-togglebutton-content) {
	padding: 0.25rem 0.5rem;
}
.interval-toggle :deep(.p-togglebutton:not(.p-togglebutton-checked):hover) {
	background-color: rgb(243 244 246);
}
.interval-toggle :deep(.p-togglebutton-checked .p-togglebutton-content) {
	background-color: var(--app-accent);
	color: var(--app-accent-text);
	box-shadow: 0 1px 2px rgb(0 0 0 / 0.08);
}
.interval-toggle :deep(.p-togglebutton-checked:hover .p-togglebutton-content) {
	background-color: var(--app-accent-strong);
}
.interval-toggle :deep(.p-togglebutton:focus-visible) {
	outline: 2px solid var(--app-accent);
	outline-offset: 1px;
}
</style>

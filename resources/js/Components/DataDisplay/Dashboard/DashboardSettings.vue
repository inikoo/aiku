<script setup lang="ts">
import { inject, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"

const props = defineProps<{
	groupCurrencySymbol: string
	intervalOptions: {
		label: string
		value: string
	}[]
	settings: {
		selected_interval: string
	}
}>()


const layout = inject('layout', layoutStructure)

// Section: Interval
const isLoadingInterval = ref<string | null>(null)
const updateInterval = (interval_code: string) => {
	router.patch(route("grp.models.user.update", layout.user?.id), {
		settings: {
			selected_interval: interval_code,
		},
	}, {
		onStart: () => {
			isLoadingInterval.value = interval_code
		},
		onFinish: () => {
			isLoadingInterval.value = null
		},
		preserveScroll: true,
	})
}
</script>

<template>
	<div class="relative mt-2 asdzxc">
		<!-- Section Setting -->
		<!-- <div class="flex justify-end items-center space-x-4">
			<div class="flex items-center space-x-4">
				<p
					class="font-medium transition-opacity"
					:class="{ 'opacity-60': isOrganisation }">
					{{ props.groupStats.currency.symbol }}
				</p>

				<ToggleSwitch
					v-model="isOrganisation"
					class="mx-2"
					@change="toggleCurrency" />

				<p
					class="font-medium transition-opacity"
					:class="{ 'opacity-60': !isOrganisation }">
					{{ organisationSymbols }}
				</p>
			</div>
		</div> -->

		<nav class="isolate flex rounded-full bg-white-50 border border-gray-200 p-1"
			aria-label="Tabs">
			<div
				v-for="(interval, idxInterval) in intervalOptions"
				:key="idxInterval"
				@click="updateInterval(interval.value)"
				:class="[
					interval.value === settings.selected_interval
						? 'bg-indigo-500 text-white font-medium'
						: 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
				]"
				class="relative flex-1 rounded-full py-2 px-4 text-center text-sm cursor-pointer select-none transition duration-200">
				<span :class="isLoadingInterval == interval.value ? 'opacity-0' : ''">{{ interval.value }}</span>
				<span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" :class="isLoadingInterval == interval.value ? '' : 'opacity-0'"><LoadingIcon /></span>
			</div>
		</nav>
	</div>
</template>

<style>
</style>

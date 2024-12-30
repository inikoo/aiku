<script setup lang="ts">
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { inject } from "vue"
import CountUp from "vue-countup-v3"
import { Link } from "@inertiajs/vue3"
import Icon from "@/Components/Icon.vue"
import { routeType } from "@/types/route"

const props = defineProps<{
	box_stats: {
		name: string
		value: number
		route: routeType
		icon: {
			icon: string
			tooltip: string
		}
	}[]
}>()

const locale = inject("locale", aikuLocaleStructure)
</script>

<template>
	<div class="flex gap-x-3 gap-y-4 p-4 flex-wrap">
		<Link v-for="stats in box_stats"
			:key="stats.route?.name"
			:href="stats.route?.name ? route(stats.route.name, stats.route.parameters) : '#'"
			class="bg-gray-50 min-w-64 border border-gray-300 rounded-md p-6 block hover:bg-gray-100"
		>
			<div class="flex justify-between items-center mb-1">
				<div class="capitalize">{{ stats.name }}</div>
				<Icon :data="stats.icon" class="text-xl text-gray-400" />
				<!-- <FontAwesomeIcon :icon="stats.icon.icon" v-tooltip="stats.icon.tooltip" class="text-xl text-gray-400"
					fixed-width aria-hidden="true" /> -->
			</div>

			<div class="mb-1 text-2xl font-semibold">
				<CountUp :endVal="stats.value" :duration="1.5" :scrollSpyOnce="true" :options="{
					formattingFn: (value: number) => locale.number(value)
				}" />
			</div>
		</Link>
	</div>
</template>

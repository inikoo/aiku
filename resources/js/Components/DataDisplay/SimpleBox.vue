<script setup lang="ts">
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { inject } from "vue"
import CountUp from "vue-countup-v3"
import { Link } from "@inertiajs/vue3"

const props = defineProps<{
	data: {
		label: string
		value: number
	}[]
	link: any
}>()

const locale = inject("locale", aikuLocaleStructure)
</script>

<template>
	<div class="flex gap-x-3 gap-y-4 p-4 flex-wrap">
		<Link
			v-for="fake in link.meta"
			:key="fake.route.name"
			:href="route(fake.route.name, fake.route.parameters)"
			class="bg-gray-50 min-w-64 border border-gray-300 rounded-md p-6 block hover:bg-gray-100"
		>
			<div class="flex justify-between items-center mb-1">
				<div>{{ fake.name }}</div>
				<FontAwesomeIcon
					:icon="fake.leftIcon.icon"
					v-tooltip="fake.leftIcon.tooltip"
					class="text-xl text-gray-400"
					fixed-width
					aria-hidden="true" 
				/>
			</div>

			<div class="mb-1 text-2xl font-semibold">
				<CountUp
					:endVal="fake.number"
					:duration="1.5"
					:scrollSpyOnce="true"
					:options="{
						formattingFn: (value: number) => locale.number(value)
					}"
				/>
			</div>
		</Link>
	</div>
</template>


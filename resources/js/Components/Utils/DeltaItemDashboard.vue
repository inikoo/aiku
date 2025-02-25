<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps<{
	dataTable?: any
	type?: string
	section?: string
	totalAmount?: any
	totalTooltip?: any
}>()
</script>

<template>
	<div class="flex justify-end relative">
		<Transition name="spin-to-down" mode="out-in">
			<div
				v-if="section === 'body'"
				v-tooltip="dataTable?.interval_percentages?.[type]?.tooltip || ''"
				:key="`${dataTable?.interval_percentages?.[type]?.difference}_${dataTable?.interval_percentages?.[type]?.percentage}`"
				style="align-items: center"
				class="whitespace-nowrap">
				<span  class="text-[14px] md:text-[16px] font-mono pr-1">
					{{
						dataTable?.interval_percentages?.[type]?.percentage
							? `${
									dataTable.interval_percentages[type].percentage > 0 ? "+" : ""
							  }${dataTable.interval_percentages[type].percentage.toFixed(2)}%`
							: `0.0%`
					}}
				</span>
				<FontAwesomeIcon
					v-if="dataTable?.interval_percentages?.[type]?.percentage"
					class="text-[9px] md:text-[16px] opacity-60"
					:icon="
						dataTable.interval_percentages[type].percentage < 0
							? 'fas fa-play'
							: 'fas fa-play'
					"
					:class="
						dataTable.interval_percentages[type].percentage < 0
							? 'text-red-500 rotate-90'
							: 'text-green-500 rotate-[-90deg]'
					" />
			</div>
			<div v-else-if="section === 'footer'">
				<span
					v-tooltip="totalTooltip?.[type] || ''"
					class="md:text-[16px] text-[14px] font-mono pr-1"
					>
					{{
						totalAmount?.[type + "_percentages"]
							? `${totalAmount[type + "_percentages"] > 0 ? "+" : ""}${totalAmount[
									type + "_percentages"
							  ].toFixed(2)}%`
							: "0.0%"
					}}
				</span>
				<FontAwesomeIcon
					v-if="totalAmount?.[type + '_percentages']"
					:icon="totalAmount[type + '_percentages'] < 0 ? 'fas fa-play' : 'fas fa-play'"
					class="md:text-[16px] text-[9px] opacity-70"
					:class="
						totalAmount[type + '_percentages'] < 0
							? 'text-red-500 rotate-90'
							: 'text-green-500 rotate-[-90deg]'
					" />
				<div v-else style="width: 10px"></div>
			</div>
		</Transition>
	</div>
</template>

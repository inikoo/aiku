<script setup lang="ts">
import { computed } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClock, faCheckCircle, faTimesCircle, faCircle } from "@fal"

library.add(faClock, faCheckCircle, faTimesCircle, faCircle)

const props = defineProps<{
	status: string
	statusLabel?: string
}>()

const tones: Record<string, { card: string; header: string; pill: string; icon: any }> = {
	pending: {
		card: "border-l-amber-500 shadow",
		header: "bg-amber-50",
		pill: "bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-300",
		icon: faClock,
	},
	approved: {
		card: "border-l-green-400 shadow-sm",
		header: "bg-green-50/60",
		pill: "bg-green-100 text-green-800",
		icon: faCheckCircle,
	},
	rejected: {
		card: "border-l-red-400 shadow-sm",
		header: "bg-red-50/60",
		pill: "bg-red-100 text-red-800",
		icon: faTimesCircle,
	},
}

const neutralTone = {
	card: "border-l-gray-300 shadow-sm",
	header: "bg-gray-50",
	pill: "bg-gray-100 text-gray-600",
	icon: faCircle,
}

const tone = computed(() => tones[props.status] ?? neutralTone)
</script>

<template>
	<article class="overflow-hidden rounded-lg border border-l-4 border-gray-200 bg-white" :class="tone.card" :data-status="status">
		<header class="flex items-start justify-between gap-3 px-4 py-3" :class="tone.header">
			<div class="flex min-w-0 items-center gap-3">
				<slot name="avatar" />
				<div class="min-w-0">
					<div class="break-words font-semibold text-gray-900">
						<slot name="title" />
					</div>
					<div v-if="$slots.meta" class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-600">
						<slot name="meta" />
					</div>
				</div>
			</div>
			<span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold" :class="tone.pill">
				<FontAwesomeIcon :icon="tone.icon" fixed-width aria-hidden="true" />
				{{ statusLabel ?? status }}
			</span>
		</header>

		<div class="px-4 py-4">
			<slot />
		</div>

		<footer
			v-if="$slots.actions"
			class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3"
			:class="{ 'bg-amber-50/40': status === 'pending' }">
			<slot name="actions" />
		</footer>
	</article>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue"

type ImageSources = { original?: string | null } & Record<string, string | null | undefined>

const props = withDefaults(
	defineProps<{
		name?: string | null
		image?: string | ImageSources | null
		size?: "xs" | "sm" | "md"
	}>(),
	{ name: null, image: null, size: "sm" }
)

const sizeClasses: Record<string, string> = {
	xs: "h-6 w-6 text-[10px]",
	sm: "h-8 w-8 text-xs",
	md: "h-10 w-10 text-sm",
}

const source = computed(() => (typeof props.image === "string" ? props.image : props.image?.original) || null)

const hasFailed = ref(false)

watch(source, () => {
	hasFailed.value = false
})

const initials = computed(
	() =>
		(props.name ?? "")
			.split(/\s+/)
			.filter(Boolean)
			.slice(0, 2)
			.map((word) => word[0].toUpperCase())
			.join("") || "?"
)
</script>

<template>
	<span
		class="relative inline-flex shrink-0 select-none items-center justify-center overflow-hidden rounded-full bg-[--app-accent-soft] font-medium leading-none text-[--app-accent-deep]"
		:class="sizeClasses[size]"
		aria-hidden="true">
		{{ initials }}
		<img
			v-if="source && !hasFailed"
			:src="source"
			alt=""
			loading="lazy"
			class="absolute inset-0 h-full w-full object-cover"
			@error="hasFailed = true" />
	</span>
</template>

<script setup lang="ts">
const props = defineProps<{
	type: string | null
	label: string
}>()

const colors: { [type: string]: string } = {
	freight: "bg-sky-50 text-sky-700 ring-sky-200",
	shipping: "bg-sky-50 text-sky-700 ring-sky-200",
	local_transport: "bg-cyan-50 text-cyan-700 ring-cyan-200",
	duty: "bg-violet-50 text-violet-700 ring-violet-200",
	anti_dumping: "bg-purple-50 text-purple-700 ring-purple-200",
	import_levy: "bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-200",
	tax: "bg-slate-100 text-slate-700 ring-slate-200",
	customs_clearance: "bg-teal-50 text-teal-700 ring-teal-200",
	port_charges: "bg-emerald-50 text-emerald-700 ring-emerald-200",
	storage: "bg-amber-50 text-amber-700 ring-amber-200",
	inspection: "bg-lime-50 text-lime-700 ring-lime-200",
	documentation: "bg-stone-100 text-stone-700 ring-stone-200",
	insurance: "bg-pink-50 text-pink-700 ring-pink-200",
	fine: "bg-red-50 text-red-700 ring-red-200",
	other: "bg-gray-100 text-gray-700 ring-gray-200",
}
</script>

<template>
	<span class="inline-flex items-center whitespace-nowrap rounded px-1.5 py-0.5 text-xs font-medium not-italic ring-1 ring-inset" :class="colors[props.type ?? 'other'] ?? colors.other">
		{{ label }}
	</span>
</template>

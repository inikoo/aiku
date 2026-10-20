<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-20 12:00
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { reactive, watch } from "vue"
import { router } from "@inertiajs/vue3"
import InputNumber from "primevue/inputnumber"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faWeight, faExclamationTriangle, faPlus } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

defineOptions({ inheritAttrs: false })

type CheckLine = {
	id: number
	label: string
	level: string
	quantity: number
	declared_weight_g_each: number | null
	weight_g_each: number | null
	weight_is_measured: boolean
	fate_at_our_warehouse: string
}

type Check = {
	id: number
	code: string | null
	name: string | null
	spec_version: number
	spec_status: string
	spec_status_label: string
	cartons: number
	cartons_to_check: number
	weigh_materials: boolean
	expected_gross_g: number | null
	carton_weights_g: number[]
	deviation_pct: number | null
	result: string
	result_label: string
	checked_by: string | null
	checked_at: string | null
	task: string | null
	lines: CheckLine[]
	record_route: routeType | null
}

const props = defineProps<{
	data: {
		tolerance_pct: number
		items_without_spec: number
		fates: Record<string, string>
		levels: Record<string, string>
		create_route: routeType | null
		checks: Check[]
	}
	tab: string
}>()

const locale = useLocaleStore()
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const fateOptions = (fates: Record<string, string>) => Object.entries(fates).map(([value, label]) => ({ value, label }))
const formatNumber = (value: number | null | undefined, digits = 1) => (value === null || value === undefined ? "—" : locale.number(Math.round(Number(value) * 10 ** digits) / 10 ** digits))

type Entry = { cartonsKg: (number | null)[]; lines: Record<number, { weight_g_each: number | null; fate_at_our_warehouse: string }>; errors: Record<string, string>; processing: boolean }

const entries = reactive<Record<number, Entry>>({})

const fill = () => {
	for (const check of props.data.checks) {
		const slots = Math.max(check.cartons_to_check, check.carton_weights_g.length, 1)
		entries[check.id] = {
			cartonsKg: Array.from({ length: slots }, (_, index) => (check.carton_weights_g[index] ? check.carton_weights_g[index] / 1000 : null)),
			lines: Object.fromEntries(check.lines.map((line) => [line.id, { weight_g_each: line.weight_is_measured ? line.weight_g_each : null, fate_at_our_warehouse: line.fate_at_our_warehouse }])),
			errors: {},
			processing: false,
		}
	}
}
watch(() => props.data, fill, { immediate: true })

const resultClass = (result: string) =>
	({
		passed: "border-green-300 bg-green-50 text-green-700",
		mismatch: "border-red-300 bg-red-50 text-red-700",
	})[result] ?? "border-amber-300 bg-amber-50 text-amber-700"

const average = (check: Check) => {
	const weights = entries[check.id]?.cartonsKg.filter((value): value is number => !!value) ?? []
	return weights.length ? weights.reduce((total, value) => total + value, 0) / weights.length : null
}

const liveDeviation = (check: Check) => {
	const avg = average(check)
	if (!avg || !check.expected_gross_g) return null
	return ((avg * 1000 - check.expected_gross_g) / check.expected_gross_g) * 100
}

const record = (check: Check) => {
	const entry = entries[check.id]
	if (!check.record_route || !entry) return
	entry.processing = true
	router.post(
		route(check.record_route.name, check.record_route.parameters),
		{
			carton_weights_g: entry.cartonsKg.filter((value) => !!value).map((value) => Math.round(Number(value) * 1000)),
			lines: check.lines.map((line) => ({
				id: line.id,
				fate_at_our_warehouse: entry.lines[line.id].fate_at_our_warehouse,
				...(check.weigh_materials ? { weight_g_each: entry.lines[line.id].weight_g_each } : {}),
			})),
		},
		{
			preserveScroll: true,
			onError: (errors) => (entry.errors = errors),
			onSuccess: () => {
				entry.errors = {}
				router.reload({ only: [props.tab] })
			},
			onFinish: () => (entry.processing = false),
		}
	)
}

const createChecks = () => {
	if (!props.data.create_route) return
	router.post(route(props.data.create_route.name, props.data.create_route.parameters), {}, { preserveScroll: true, onSuccess: () => router.reload({ only: [props.tab] }) })
}
</script>

<template>
	<div class="max-w-6xl space-y-4 px-6 py-5 text-sm">
		<p class="text-xs text-gray-600">
			{{ ctrans("Weigh one carton in :every of each product, and on a new packing spec weigh each packing material on its own. A difference over ±:pct% sends the spec back to the agent; the goods are not affected.", { every: 20, pct: data.tolerance_pct }) }}
		</p>

		<div v-if="data.items_without_spec" class="text-xs text-amber-700">
			{{ ctrans(":count products of this delivery have no packing spec from the agent, so there is nothing to check them against.", { count: data.items_without_spec }) }}
		</div>

		<div v-if="!data.checks.length" class="rounded border border-dashed border-gray-300 px-4 py-6 text-center text-gray-500">
			{{ ctrans("No packing checks for this delivery.") }}
			<div v-if="data.create_route" class="mt-3">
				<Button type="tertiary" :label="ctrans('Look for packing specs again')" @click="createChecks">
					<template #icon><FontAwesomeIcon :icon="faPlus" class="text-[--app-accent]" fixed-width aria-hidden="true" /></template>
				</Button>
			</div>
		</div>

		<div v-for="check in data.checks" :key="check.id" class="space-y-3 rounded border border-gray-200 p-3">
			<div class="flex flex-wrap items-center gap-2">
				<span class="whitespace-nowrap font-semibold text-gray-800">{{ check.code }}</span>
				<span class="text-gray-600">{{ check.name }}</span>
				<span class="text-xs text-gray-500">· {{ ctrans("Packing spec") }} v{{ check.spec_version }} ({{ check.spec_status_label }})</span>
				<span class="text-xs text-gray-500">· {{ ctrans(":cartons cartons", { cartons: formatNumber(check.cartons) }) }}</span>
				<span v-if="check.task" class="text-xs text-gray-500">· {{ check.task }}</span>
				<span class="ml-auto rounded border px-2 py-0.5 text-xs" :class="resultClass(check.result)">{{ check.result_label }}</span>
			</div>
			<div v-if="check.checked_at" class="text-xs text-gray-500">
				{{ ctrans("Checked by :name on :date", { name: check.checked_by ?? "—", date: new Date(check.checked_at).toLocaleDateString() }) }}
				<template v-if="check.deviation_pct !== null"> · {{ ctrans("cartons :deviation% against the spec", { deviation: (check.deviation_pct > 0 ? "+" : "") + formatNumber(check.deviation_pct) }) }}</template>
			</div>

			<div class="space-y-1">
				<div class="flex items-center gap-2 text-xs text-gray-600">
					<FontAwesomeIcon :icon="faWeight" fixed-width aria-hidden="true" />
					{{ ctrans("Gross weight of :count cartons (kg)", { count: check.cartons_to_check }) }}
					<span v-if="check.expected_gross_g" class="text-gray-400">· {{ ctrans("expected :kg kg", { kg: formatNumber(check.expected_gross_g / 1000, 2) }) }}</span>
				</div>
				<div class="flex flex-wrap items-center gap-2">
					<InputNumber
						v-for="(_, index) in entries[check.id]?.cartonsKg"
						:key="index"
						v-model="entries[check.id].cartonsKg[index]"
						:min="0"
						:maxFractionDigits="3"
						:disabled="!check.record_route"
						:class="fieldFocusClass"
						:inputClass="'!w-24 !py-1 !text-xs'" />
					<span v-if="liveDeviation(check) !== null" class="text-xs" :class="Math.abs(liveDeviation(check) ?? 0) > data.tolerance_pct ? 'text-red-600' : 'text-green-700'">
						<FontAwesomeIcon v-if="Math.abs(liveDeviation(check) ?? 0) > data.tolerance_pct" :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
						{{ (liveDeviation(check)! > 0 ? "+" : "") + formatNumber(liveDeviation(check)) }}%
					</span>
				</div>
				<div v-if="entries[check.id]?.errors['carton_weights_g']" class="text-xs text-red-600">{{ entries[check.id].errors["carton_weights_g"] }}</div>
			</div>

			<table class="w-full text-xs">
				<thead class="whitespace-nowrap text-gray-500">
					<tr>
						<th class="py-1 pr-3 text-left font-normal">{{ ctrans("Material") }}</th>
						<th class="py-1 pr-3 text-left font-normal">{{ ctrans("Where") }}</th>
						<th class="py-1 pr-3 text-right font-normal">{{ ctrans("Qty") }}</th>
						<th class="py-1 pr-3 text-right font-normal">{{ ctrans("Declared g each") }}</th>
						<th v-if="check.weigh_materials" class="py-1 pr-3 text-left font-normal">{{ ctrans("Weighed g each") }}</th>
						<th class="py-1 text-left font-normal">{{ ctrans("What we do with it") }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="line in check.lines" :key="line.id" class="border-t border-gray-100">
						<td class="py-1 pr-3 text-gray-800">{{ line.label }}</td>
						<td class="py-1 pr-3 text-gray-600">{{ data.levels[line.level] }}</td>
						<td class="py-1 pr-3 text-right tabular-nums">{{ formatNumber(line.quantity, 3) }}</td>
						<td class="py-1 pr-3 text-right tabular-nums">{{ formatNumber(line.declared_weight_g_each, 2) }}</td>
						<td v-if="check.weigh_materials" class="py-1 pr-3">
							<InputNumber v-model="entries[check.id].lines[line.id].weight_g_each" :min="0" :maxFractionDigits="3" :disabled="!check.record_route" :class="fieldFocusClass" :inputClass="'!w-24 !py-1 !text-xs'" />
						</td>
						<td class="py-1">
							<Select v-model="entries[check.id].lines[line.id].fate_at_our_warehouse" :options="fateOptions(data.fates)" optionLabel="label" optionValue="value" :disabled="!check.record_route" :class="fieldFocusClass" size="small" class="min-w-48" />
						</td>
					</tr>
				</tbody>
			</table>
			<div v-if="entries[check.id]?.errors['lines']" class="text-xs text-red-600">{{ entries[check.id].errors["lines"] }}</div>

			<Button v-if="check.record_route" type="tertiary" :label="check.checked_at ? ctrans('Save again') : ctrans('Save check')" :loading="entries[check.id]?.processing" @click="record(check)">
				<template #icon><FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" /></template>
			</Button>
		</div>
	</div>
</template>

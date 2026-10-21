<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-20 13:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link, router } from "@inertiajs/vue3"
import DatePicker from "primevue/datepicker"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faBoxOpen, faCheck } from "@fal"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

type Bucket = { products: number; cartons: number; kg: number; estimated_kg: number | null; unknown: number | null }

const props = defineProps<{
	data?: {
		from: string
		to: string
		scheme: string | null
		scheme_label: string | null
		has_data: boolean
		kg: number
		measured_kg: number
		in_product_packaging_kg: number
		activities: Record<string, string>
		levels: Record<string, string>
		materials: Record<string, string>
		matrix: { activity: string; level: string; material: string; kg: number }[]
		products: { code: string; name: string | null; agent: string | null; kg: number; spec_status: string | null; measured: boolean; org_supplier_product_slug: string | null }[]
		deliveries: { reference: string; slug: string; date: string; agent: string | null; products: number; kg: number }[]
		coverage: {
			buckets: { verified: Bucket; declared: Bucket; no_spec: Bucket }
			cartons: number
			average_kg_per_carton: number | null
			coverage_pct: number | null
			to_declare: { code: string; name: string | null; agent: string | null; cartons: number; estimated_kg: number | null; org_supplier_product_slug: string | null }[]
		}
		league: {
			id: number
			code: string
			name: string
			products: number
			cartons: number
			declared: number
			verified: number
			returned: number
			in_review: number
			coverage_pct: number | null
			cut_off: string | null
			cut_off_route: routeType | null
		}[]
	}
}>()

const locale = useLocaleStore()
const organisation = computed(() => route().params.organisation as string)
const kg = (value: number | null | undefined) => (value === null || value === undefined ? "—" : locale.number(Math.round(value * 10) / 10))
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const datePickerPt = { pcInputText: { root: { class: "!w-32 !py-1 !text-xs" } } }

const rows = computed(() => {
	const grouped: Record<string, { activity: string; level: string; cells: Record<string, number>; total: number }> = {}
	for (const cell of props.data?.matrix ?? []) {
		const key = `${cell.activity}|${cell.level}`
		grouped[key] ??= { activity: cell.activity, level: cell.level, cells: {}, total: 0 }
		grouped[key].cells[cell.material] = (grouped[key].cells[cell.material] ?? 0) + cell.kg
		grouped[key].total += cell.kg
	}
	const activityOrder = Object.keys(props.data?.activities ?? {})
	const levelOrder = Object.keys(props.data?.levels ?? {})
	return Object.values(grouped).sort((a, b) => activityOrder.indexOf(a.activity) - activityOrder.indexOf(b.activity) || levelOrder.indexOf(a.level) - levelOrder.indexOf(b.level))
})

const materialTotal = (material: string) => rows.value.reduce((total, row) => total + (row.cells[material] ?? 0), 0)

const drill = ref<"products" | "deliveries" | "to_declare">("to_declare")
const drillOptions = computed(() => [
	{ label: ctrans("To declare"), value: "to_declare" },
	{ label: ctrans("Products"), value: "products" },
	{ label: ctrans("Deliveries"), value: "deliveries" },
])

const productUrl = (slug: string | null) => (slug ? route("grp.org.procurement.org_supplier_products.show", { organisation: organisation.value, orgSupplierProduct: slug, tab: "packing" }) : null)

const toDate = (iso: string | null) => {
	if (!iso) return null
	const [year, month, day] = iso.slice(0, 10).split("-").map(Number)
	return new Date(year, month - 1, day)
}
const toIso = (date: Date | null) => (date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null)

const setCutOff = (agent: { cut_off_route: routeType | null }, value: Date | null) => {
	if (!agent.cut_off_route) return
	router.patch(route(agent.cut_off_route.name, agent.cut_off_route.parameters), { cut_off: toIso(value) }, { preserveScroll: true, preserveState: true, only: ["inbound"] })
}

const bucketLabel: Record<string, string> = {
	verified: ctrans("Verified"),
	declared: ctrans("Declared by the agent"),
	no_spec: ctrans("No packing spec"),
}
const bucketClass: Record<string, string> = {
	verified: "border-green-300",
	declared: "border-gray-300",
	no_spec: "border-amber-300",
}
</script>

<template>
	<div class="space-y-6 px-4 py-5 text-sm">
		<div v-if="!data" class="h-40 animate-pulse rounded-lg bg-gray-100" />

		<template v-else>
			<div class="flex flex-wrap items-end gap-x-8 gap-y-2">
				<div>
					<div class="text-xs text-gray-500">{{ ctrans("Packing material inside received cartons") }}</div>
					<div class="text-2xl font-semibold tabular-nums text-gray-900">{{ ctrans(":kg kg", { kg: kg(data.kg) }) }}</div>
				</div>
				<div class="text-xs text-gray-500">
					{{ ctrans(":kg kg weighed by goods in, the rest as the agents declared it", { kg: kg(data.measured_kg) }) }}
					<div v-if="data.in_product_packaging_kg > 0">
						{{ ctrans("Not counted: :kg kg passed on to the buyer that the products' own packaging (Compliance tab) already weighs", { kg: kg(data.in_product_packaging_kg) }) }}
					</div>
					<template v-if="data.scheme_label"> · {{ data.scheme_label }}</template>
				</div>
			</div>

			<!-- Coverage -->
			<div class="space-y-2">
				<div class="text-xs font-semibold text-gray-700">
					{{ ctrans("Coverage") }}
					<span v-if="data.coverage.coverage_pct !== null" class="font-normal text-gray-500">· {{ ctrans(":pct% of :cartons cartons received have a packing spec", { pct: locale.number(data.coverage.coverage_pct), cartons: kg(data.coverage.cartons) }) }}</span>
				</div>
				<div class="grid gap-3 md:grid-cols-3">
					<div v-for="(bucket, key) in data.coverage.buckets" :key="key" class="rounded-lg border bg-white p-3" :class="bucketClass[key]">
						<div class="text-xs text-gray-500">{{ bucketLabel[key] }}</div>
						<div class="mt-1 text-lg font-semibold tabular-nums text-gray-900">
							<template v-if="key === 'no_spec'">≈ {{ ctrans(":kg kg", { kg: kg(bucket.estimated_kg) }) }}</template>
							<template v-else>{{ ctrans(":kg kg", { kg: kg(bucket.kg) }) }}</template>
						</div>
						<div class="text-xs text-gray-500">
							{{ ctrans(":products products, :cartons cartons", { products: bucket.products, cartons: kg(bucket.cartons) }) }}
							<template v-if="key === 'no_spec'">
								<br />
								<template v-if="data.coverage.average_kg_per_carton !== null">{{ ctrans("Estimated from carton weight less net weight, else :kg kg per carton as declared for the rest", { kg: locale.number(data.coverage.average_kg_per_carton) }) }}</template>
								<template v-else>{{ ctrans("Estimated from carton weight less net weight") }}</template>
								<template v-if="bucket.unknown">; {{ ctrans(":count products have nothing to estimate from yet", { count: bucket.unknown }) }}</template>
							</template>
						</div>
					</div>
				</div>
			</div>

			<!-- Matrix -->
			<div v-if="data.has_data" class="overflow-x-auto rounded-lg bg-white ring-1 ring-gray-200">
				<table class="min-w-full text-xs">
					<thead class="whitespace-nowrap text-gray-600">
						<tr class="text-left">
							<th class="px-4 py-2 font-medium">{{ ctrans("Activity") }}</th>
							<th class="px-2 py-2 font-medium">{{ ctrans("Level") }}</th>
							<th v-for="(label, material) in data.materials" :key="material" class="px-2 py-2 text-right font-medium">{{ label }}</th>
							<th class="px-4 py-2 text-right font-medium">{{ ctrans("Total kg") }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 tabular-nums">
						<tr v-for="row in rows" :key="`${row.activity}|${row.level}`">
							<td class="px-4 py-2 text-gray-900">{{ data.activities[row.activity] ?? row.activity }}</td>
							<td class="px-2 py-2 text-gray-600">{{ data.levels[row.level] ?? row.level }}</td>
							<td v-for="(_, material) in data.materials" :key="material" class="px-2 py-2 text-right">{{ row.cells[material] ? kg(row.cells[material]) : "" }}</td>
							<td class="px-4 py-2 text-right font-medium text-gray-900">{{ kg(row.total) }}</td>
						</tr>
						<tr class="bg-gray-50 font-medium">
							<td class="px-4 py-2" colspan="2">{{ ctrans("Total") }}</td>
							<td v-for="(_, material) in data.materials" :key="material" class="px-2 py-2 text-right">{{ kg(materialTotal(material as string)) }}</td>
							<td class="px-4 py-2 text-right">{{ kg(data.kg) }}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div v-else class="rounded-lg border border-dashed border-gray-300 px-6 py-8 text-center text-gray-500">
				<FontAwesomeIcon :icon="faBoxOpen" class="mb-2 text-2xl text-gray-300" fixed-width aria-hidden="true" />
				<div>{{ ctrans("No inbound packing in this period yet: nothing received had a packing spec, or the flows have not been rebuilt since.") }}</div>
			</div>

			<!-- Drill-down -->
			<div class="space-y-2">
				<SegmentedToggle v-model="drill" :options="drillOptions" :ariaLabel="ctrans('Show')" />

				<table v-if="drill === 'to_declare'" class="min-w-full text-xs">
					<thead class="whitespace-nowrap text-gray-600">
						<tr class="text-left">
							<th class="px-2 py-1 font-medium">{{ ctrans("Product") }}</th>
							<th class="px-2 py-1 font-medium">{{ ctrans("Agent") }}</th>
							<th class="px-2 py-1 text-right font-medium">{{ ctrans("Cartons") }}</th>
							<th class="px-2 py-1 text-right font-medium">{{ ctrans("Estimated kg") }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 tabular-nums">
						<tr v-for="product in data.coverage.to_declare" :key="product.code">
							<td class="px-2 py-1">
								<Link v-if="productUrl(product.org_supplier_product_slug)" :href="productUrl(product.org_supplier_product_slug)!" class="primaryLink whitespace-nowrap">{{ product.code }}</Link>
								<span v-else class="whitespace-nowrap">{{ product.code }}</span>
								<span class="text-gray-500"> {{ product.name }}</span>
							</td>
							<td class="px-2 py-1 whitespace-nowrap">{{ product.agent ?? "—" }}</td>
							<td class="px-2 py-1 text-right">{{ kg(product.cartons) }}</td>
							<td class="px-2 py-1 text-right">{{ kg(product.estimated_kg) }}</td>
						</tr>
						<tr v-if="!data.coverage.to_declare.length"><td colspan="4" class="px-2 py-3 text-gray-400">{{ ctrans("Everything received has a packing spec") }}</td></tr>
					</tbody>
				</table>

				<table v-else-if="drill === 'products'" class="min-w-full text-xs">
					<thead class="whitespace-nowrap text-gray-600">
						<tr class="text-left">
							<th class="px-2 py-1 font-medium">{{ ctrans("Product") }}</th>
							<th class="px-2 py-1 font-medium">{{ ctrans("Agent") }}</th>
							<th class="px-2 py-1 font-medium">{{ ctrans("Spec") }}</th>
							<th class="px-2 py-1 text-right font-medium">{{ ctrans("kg") }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 tabular-nums">
						<tr v-for="product in data.products" :key="product.code">
							<td class="px-2 py-1">
								<Link v-if="productUrl(product.org_supplier_product_slug)" :href="productUrl(product.org_supplier_product_slug)!" class="primaryLink whitespace-nowrap">{{ product.code }}</Link>
								<span v-else class="whitespace-nowrap">{{ product.code }}</span>
								<span class="text-gray-500"> {{ product.name }}</span>
							</td>
							<td class="px-2 py-1 whitespace-nowrap">{{ product.agent ?? "—" }}</td>
							<td class="px-2 py-1 whitespace-nowrap">
								{{ product.spec_status }}
								<FontAwesomeIcon v-if="product.measured" :icon="faCheck" class="text-green-600" fixed-width :title="ctrans('Weighed by goods in')" />
							</td>
							<td class="px-2 py-1 text-right">{{ kg(product.kg) }}</td>
						</tr>
					</tbody>
				</table>

				<table v-else class="min-w-full text-xs">
					<thead class="whitespace-nowrap text-gray-600">
						<tr class="text-left">
							<th class="px-2 py-1 font-medium">{{ ctrans("Delivery") }}</th>
							<th class="px-2 py-1 font-medium">{{ ctrans("Date") }}</th>
							<th class="px-2 py-1 font-medium">{{ ctrans("Agent") }}</th>
							<th class="px-2 py-1 text-right font-medium">{{ ctrans("Products") }}</th>
							<th class="px-2 py-1 text-right font-medium">{{ ctrans("kg") }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 tabular-nums">
						<tr v-for="delivery in data.deliveries" :key="delivery.slug">
							<td class="px-2 py-1">
								<Link :href="route('grp.org.procurement.stock_deliveries.show', { organisation, stockDelivery: delivery.slug })" class="primaryLink whitespace-nowrap">{{ delivery.reference }}</Link>
							</td>
							<td class="px-2 py-1 whitespace-nowrap">{{ new Date(delivery.date).toLocaleDateString() }}</td>
							<td class="px-2 py-1 whitespace-nowrap">{{ delivery.agent ?? "—" }}</td>
							<td class="px-2 py-1 text-right">{{ delivery.products }}</td>
							<td class="px-2 py-1 text-right">{{ kg(delivery.kg) }}</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Agent league table -->
			<div class="space-y-2">
				<div class="text-xs font-semibold text-gray-700">
					{{ ctrans("Agents") }}
					<span class="font-normal text-gray-500">· {{ ctrans("products received in the last 12 months, most cartons first") }}</span>
				</div>
				<div class="overflow-x-auto rounded-lg bg-white ring-1 ring-gray-200">
					<table class="min-w-full text-xs">
						<thead class="whitespace-nowrap text-gray-600">
							<tr class="text-left">
								<th class="px-4 py-2 font-medium">{{ ctrans("Agent") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Products") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Cartons") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Declared") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Verified") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Returned") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("In review") }}</th>
								<th class="px-2 py-2 text-right font-medium">{{ ctrans("Coverage") }}</th>
								<th class="px-4 py-2 font-medium">{{ ctrans("Block orders from") }}</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-gray-100 tabular-nums">
							<tr v-for="agent in data.league" :key="agent.id">
								<td class="px-4 py-2 whitespace-nowrap"><span class="font-medium text-gray-900">{{ agent.code }}</span> <span class="text-gray-500">{{ agent.name }}</span></td>
								<td class="px-2 py-2 text-right">{{ agent.products }}</td>
								<td class="px-2 py-2 text-right">{{ locale.number(agent.cartons) }}</td>
								<td class="px-2 py-2 text-right">{{ agent.declared }}</td>
								<td class="px-2 py-2 text-right">{{ agent.verified }}</td>
								<td class="px-2 py-2 text-right" :class="agent.returned ? 'text-amber-700' : ''">{{ agent.returned }}</td>
								<td class="px-2 py-2 text-right">{{ agent.in_review }}</td>
								<td class="px-2 py-2 text-right font-medium">{{ agent.coverage_pct === null ? "—" : `${locale.number(agent.coverage_pct)}%` }}</td>
								<td class="px-4 py-2">
									<DatePicker
										v-if="agent.cut_off_route"
										:modelValue="toDate(agent.cut_off)"
										dateFormat="d M yy"
										showButtonBar
										showIcon
										iconDisplay="input"
										:placeholder="ctrans('Never')"
										:class="fieldFocusClass"
										:pt="datePickerPt"
										@update:modelValue="(value) => setCutOff(agent, value as Date | null)" />
									<span v-else class="whitespace-nowrap">{{ agent.cut_off ? new Date(agent.cut_off).toLocaleDateString() : ctrans("Never") }}</span>
								</td>
							</tr>
							<tr v-if="!data.league.length"><td colspan="9" class="px-4 py-3 text-gray-400">{{ ctrans("No deliveries from agents in the last 12 months") }}</td></tr>
						</tbody>
					</table>
				</div>
			</div>
		</template>
	</div>
</template>

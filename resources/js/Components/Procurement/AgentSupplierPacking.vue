<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, inject, reactive, ref } from "vue"
import { Link, router } from "@inertiajs/vue3"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBoxOpen, faCheck } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import InformationIcon from "@/Components/Utils/InformationIcon.vue"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { routeType } from "@/types/route"

library.add(faBoxOpen, faCheck)

type SupplierRow = { id?: number; code: string; name: string; spend: number; route: routeType | null }

const props = defineProps<{
	packing: {
		split: Record<string, number>
		labels: Record<string, string>
		packaging: SupplierRow[]
		packaging_spend: number
		unknown: SupplierRow[]
		candidates: { value: number; label: string }[]
	}
	currency: string
	canEdit: boolean
}>()

const locale = inject("locale", aikuLocaleStructure)
const money = (amount: number) => locale.currencyFormat(props.currency, Math.round(amount))
const href = (target: routeType) => route(target.name, target.parameters)

const splitTotal = computed(() => Object.values(props.packing.split).reduce((sum, value) => sum + value, 0))
const splitColours: Record<string, string> = { supplier_packs: "bg-gray-600", agent_packs: "bg-gray-400", third_party: "bg-amber-400", unknown: "bg-gray-200" }
const splitRows = computed(() => Object.entries(props.packing.split).map(([key, value]) => ({ key, value, label: key === "unknown" ? ctrans("Not recorded") : props.packing.labels[key] })))
const methodOptions = computed(() => Object.entries(props.packing.labels).map(([value, label]) => ({ value, label })))

const choices = reactive<Record<number, { method: string | null; packagingSupplierId: number | null }>>({})
const choice = (id: number) => (choices[id] ??= { method: null, packagingSupplierId: null })
const saving = ref<number | null>(null)

function save(supplier: SupplierRow) {
	const selected = choice(supplier.id as number)
	router.patch(route("grp.models.supplier.update", { supplier: supplier.id }), {
		packing_method: selected.method,
		packaging_supplier_id: selected.method === "third_party" ? selected.packagingSupplierId : null,
	}, {
		only: ["dashboard"],
		preserveScroll: true,
		onStart: () => (saving.value = supplier.id as number),
		onFinish: () => (saving.value = null),
	})
}
</script>

<template>
	<section id="packing" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
		<header class="flex items-center gap-1.5 border-b border-gray-200 px-4 py-3">
			<FontAwesomeIcon icon="fal fa-box-open" class="text-gray-400" fixed-width aria-hidden="true" />
			<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Extra packing") }}</h2>
			<InformationIcon :information="ctrans('Who packs the goods of the suppliers ordered from in the last 12 months, and what was spent with packaging suppliers. Products can set their own packing on their edit page.')" class="text-xs text-gray-500" />
		</header>
		<div class="space-y-4 p-4">
			<div>
				<div v-if="splitTotal" class="flex h-3 w-full overflow-hidden rounded-full bg-gray-100">
					<div v-for="row in splitRows" :key="row.key" v-tooltip="`${row.label}: ${row.value}`" :class="splitColours[row.key]" :style="{ width: `${(row.value / splitTotal) * 100}%` }" />
				</div>
				<div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
					<span v-for="row in splitRows" :key="row.key" class="flex items-center gap-1.5">
						<span class="h-2 w-2 rounded-full" :class="splitColours[row.key]" />
						{{ row.label }} <span class="tabular-nums text-gray-900">{{ row.value }}</span>
					</span>
				</div>
			</div>

			<div>
				<h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Packaging suppliers, :amount in 12 months", { amount: money(packing.packaging_spend) }) }}</h3>
				<p v-if="!packing.packaging.length" class="text-sm italic text-gray-400">{{ ctrans("No supplier is recorded as packing for another yet") }}</p>
				<ul v-else class="divide-y divide-gray-100 text-sm">
					<li v-for="supplier in packing.packaging" :key="supplier.code" class="flex items-center justify-between gap-2 py-1.5">
						<span class="truncate">
							<Link v-if="supplier.route" :href="href(supplier.route)" class="font-medium text-gray-700 hover:underline">{{ supplier.name }}</Link>
							<span class="ml-1 text-xs text-gray-400">{{ supplier.code }}</span>
						</span>
						<span class="shrink-0 tabular-nums text-gray-700">{{ money(supplier.spend) }}</span>
					</li>
				</ul>
			</div>

			<div v-if="packing.unknown.length">
				<h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Packing not recorded, :count suppliers ordered from in 12 months", { count: packing.unknown.length }) }}</h3>
				<ul class="max-h-80 divide-y divide-gray-100 overflow-auto text-sm">
					<li v-for="supplier in packing.unknown" :key="supplier.id" class="flex flex-wrap items-center justify-between gap-2 py-1.5">
						<span class="min-w-0 flex-1 truncate">
							<Link v-if="supplier.route" :href="href(supplier.route)" class="font-medium text-gray-700 hover:underline">{{ supplier.name }}</Link>
							<span class="ml-1 text-xs text-gray-400">{{ supplier.code }} · {{ money(supplier.spend) }}</span>
						</span>
						<span v-if="canEdit" class="flex items-center gap-2">
							<Select v-model="choice(supplier.id as number).method" :options="methodOptions" optionLabel="label" optionValue="value" size="small" :placeholder="ctrans('Who packs')" class="w-44" />
							<Select
								v-if="choice(supplier.id as number).method === 'third_party'"
								v-model="choice(supplier.id as number).packagingSupplierId"
								:options="packing.candidates.filter((candidate) => candidate.value !== supplier.id)"
								optionLabel="label"
								optionValue="value"
								filter
								size="small"
								:placeholder="ctrans('Packaging supplier')"
								class="w-52" />
							<Button
								type="tertiary"
								size="xs"
								:label="ctrans('Save')"
								:loading="saving === supplier.id"
								:disabled="!choice(supplier.id as number).method || (choice(supplier.id as number).method === 'third_party' && !choice(supplier.id as number).packagingSupplierId)"
								@click="save(supplier)">
								<template #icon>
									<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
								</template>
							</Button>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</section>
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"

const props = defineProps<{
	data: {}
	currency: string
	tab?: string
}>()

const problemClass: Record<string, string> = {
	out: "bg-red-100 text-red-700",
	low: "bg-amber-100 text-amber-700",
	dead: "bg-gray-200 text-gray-600",
}

const RATING_BAD = 4
const REFUND_BAD = 5
const SHORT_BAD = 10

const money = (amount: number) => new Intl.NumberFormat(undefined, { style: "currency", currency: props.currency, maximumFractionDigits: 0 }).format(Math.round(amount))
const shortMoney = (amount: number) => new Intl.NumberFormat(undefined, { style: "currency", currency: props.currency, notation: "compact", maximumFractionDigits: 1 }).format(amount)
const signed = (percent: number) => `${percent > 0 ? "+" : ""}${percent.toFixed(1)}%`
const supplierRoute = (product: { supplier_slug: string }) => route("grp.org.agent.sales.suppliers.show", [route().params["organisation"], product.supplier_slug])
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(supplier_code)="{ item: product }">
			<Link :href="supplierRoute(product)" class="text-gray-500 hover:text-gray-700">{{ product.supplier_code }}</Link>
		</template>
		<template #cell(sold)="{ item: product }"><span class="tabular-nums">{{ money(product.sold) }}</span></template>
		<template #cell(last_year)="{ item: product }"><span class="tabular-nums text-gray-500">{{ money(product.last_year) }}</span></template>
		<template #cell(change_percent)="{ item: product }">
			<span v-if="product.change_percent !== null" class="tabular-nums" :class="product.change_percent < 0 ? 'text-red-600' : 'text-green-600'">{{ signed(product.change_percent) }}</span>
		</template>
		<template #cell(stock_value)="{ item: product }"><span class="tabular-nums">{{ money(product.stock_value) }}</span></template>
		<template #cell(organisations)="{ item: product }">
			<div class="flex flex-wrap gap-1">
				<span
					v-for="organisation in product.organisations"
					:key="organisation.code"
					class="rounded px-1.5 py-0.5 text-xs tabular-nums"
					:class="organisation.problem ? problemClass[organisation.problem] : 'bg-gray-100 text-gray-600'">
					{{ organisation.code }} {{ shortMoney(organisation.sold) }}
				</span>
			</div>
		</template>
		<template #cell(rating)="{ item: row }">
			<span v-if="row.rating !== null" class="tabular-nums" :class="row.rating < RATING_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.rating.toFixed(1) }} <span class="text-gray-400">({{ row.reviews }})</span></span>
		</template>
		<template #cell(refund_percent)="{ item: row }">
			<span v-if="row.refund_percent !== null" class="tabular-nums" :class="row.refund_percent >= REFUND_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.refund_percent.toFixed(1) }}%</span>
		</template>
		<template #cell(short_percent)="{ item: row }">
			<span v-if="row.short_percent !== null" class="tabular-nums" :class="row.short_percent >= SHORT_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.short_percent.toFixed(1) }}% <span class="text-gray-400">({{ row.short_lines }})</span></span>
		</template>
	</Table>
</template>

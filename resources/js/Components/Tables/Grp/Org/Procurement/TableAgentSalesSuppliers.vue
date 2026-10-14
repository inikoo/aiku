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

const RATING_BAD = 4
const REFUND_BAD = 5
const SHORT_BAD = 10

const money = (amount: number) => new Intl.NumberFormat(undefined, { style: "currency", currency: props.currency, maximumFractionDigits: 0 }).format(Math.round(amount))
const signed = (percent: number) => `${percent > 0 ? "+" : ""}${percent.toFixed(1)}%`
const supplierRoute = (supplier: { slug: string }) => route("grp.org.agent.sales.suppliers.show", [route().params["organisation"], supplier.slug])
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(code)="{ item: supplier }">
			<Link :href="supplierRoute(supplier)" class="font-medium text-gray-500 hover:text-gray-700">{{ supplier.code }}</Link>
		</template>
		<template #cell(sold)="{ item: supplier }"><span class="tabular-nums">{{ money(supplier.sold) }}</span></template>
		<template #cell(this_year)="{ item: supplier }"><span class="tabular-nums">{{ money(supplier.this_year) }}</span></template>
		<template #cell(last_year)="{ item: supplier }"><span class="tabular-nums text-gray-500">{{ money(supplier.last_year) }}</span></template>
		<template #cell(change_percent)="{ item: supplier }">
			<span v-if="supplier.change_percent !== null" class="tabular-nums" :class="supplier.change_percent < 0 ? 'text-red-600' : 'text-green-600'">{{ signed(supplier.change_percent) }}</span>
		</template>
		<template #cell(selling)="{ item: supplier }">
			<span class="tabular-nums">{{ supplier.selling }}</span>
		</template>
		<template #cell(out_of_stock)="{ item: supplier }">
			<span class="tabular-nums" :class="supplier.out_of_stock ? 'text-red-600' : 'text-gray-400'">{{ supplier.out_of_stock }}</span>
		</template>
		<template #cell(low)="{ item: supplier }">
			<span class="tabular-nums" :class="supplier.low ? 'text-amber-600' : 'text-gray-400'">{{ supplier.low }}</span>
		</template>
		<template #cell(dead)="{ item: supplier }">
			<span class="tabular-nums text-gray-500">{{ supplier.dead }}</span>
		</template>
		<template #cell(rating)="{ item: row }">
			<span v-if="row.rating !== null" class="tabular-nums" :class="row.rating < RATING_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.rating.toFixed(1) }} <span class="text-gray-400">({{ row.reviews }})</span></span>
		</template>
		<template #cell(refund_percent)="{ item: row }">
			<span v-if="row.refund_percent !== null" class="tabular-nums" :class="row.refund_percent >= REFUND_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.refund_percent.toFixed(1) }}%</span>
		</template>
		<template #cell(faults)="{ item: row }">
			<span class="tabular-nums" :class="row.faults ? 'text-amber-600' : 'text-gray-400'">{{ row.faults }}</span>
		</template>
		<template #cell(short_percent)="{ item: row }">
			<span v-if="row.short_percent !== null" class="tabular-nums" :class="row.short_percent >= SHORT_BAD ? 'text-red-600' : 'text-gray-700'">{{ row.short_percent.toFixed(1) }}% <span class="text-gray-400">({{ row.short_lines }})</span></span>
		</template>
	</Table>
</template>

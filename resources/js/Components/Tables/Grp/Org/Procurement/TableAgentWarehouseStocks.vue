<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"

defineProps<{
	data: {}
	tab?: string
}>()

const purchaseOrderRoute = (slug: string) => route("grp.org.agent.purchase_orders.show", [route().params["organisation"], slug])
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(state)="{ item: stock }">{{ stock.state_label }}</template>
		<template #cell(purchase_order_reference)="{ item: stock }">
			<Link v-if="stock.purchase_order_slug" :href="purchaseOrderRoute(stock.purchase_order_slug)" class="text-gray-500 hover:text-gray-700">{{ stock.purchase_order_reference }}</Link>
		</template>
		<template #cell(quantity)="{ item: stock }"><span class="tabular-nums">{{ stock.quantity.toLocaleString() }}</span></template>
	</Table>
</template>

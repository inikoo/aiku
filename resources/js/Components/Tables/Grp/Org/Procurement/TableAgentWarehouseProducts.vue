<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-15 15:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faDolly, faBoxCheck, faBoxes, faTruckContainer } from "@fal"
import Table from "@/Components/Table/Table.vue"
import Icon from "@/Components/Icon.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"

library.add(faDolly, faBoxCheck, faBoxes, faTruckContainer)

defineProps<{
	data: {}
	kind: "in_production" | "received"
	tab?: string
}>()

const locale = useLocaleStore()
const organisation = route().params["organisation"]
const productRoute = (slug: string) => route("grp.org.agent.org_supplier_products.show", [organisation, slug])
const supplierRoute = (slug: string) => route("grp.org.agent.org_suppliers.show", [organisation, slug])
const purchaseOrderRoute = (slug: string) => route("grp.org.agent.purchase_orders.show", [organisation, slug])
const containerRoute = (slug: string) => route("grp.org.agent.stock_deliveries.show", [organisation, slug])

const formatQuantity = (value: number) => locale.number(Math.round(Number(value) * 1000) / 1000)
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(location)="{ item: row }">
			<div class="flex items-center gap-1.5">
				<Icon :data="row.location_icon" />
				<Link v-if="row.container_slug" :href="containerRoute(row.container_slug)" class="secondaryLink">{{ row.container_reference }}</Link>
				<span v-else class="text-gray-500">{{ ctrans("Warehouse") }}</span>
			</div>
		</template>
		<template #cell(code)="{ item: row }">
			<Link v-if="row.org_supplier_product_slug" :href="productRoute(row.org_supplier_product_slug)" class="primaryLink">{{ row.code }}</Link>
			<span v-else>{{ row.code }}</span>
		</template>
		<template #cell(supplier_code)="{ item: row }">
			<Link v-if="row.org_supplier_slug" :href="supplierRoute(row.org_supplier_slug)" class="secondaryLink">{{ row.supplier_code }}</Link>
			<span v-else>{{ row.supplier_code }}</span>
		</template>
		<template #cell(purchase_order_reference)="{ item: row }">
			<Link v-if="row.purchase_order_slug" :href="purchaseOrderRoute(row.purchase_order_slug)" class="secondaryLink">{{ row.purchase_order_reference }}</Link>
		</template>
		<template #cell(production_started_at)="{ item: row }">
			<span v-if="row.production_started_at" class="whitespace-nowrap">{{ useFormatTime(row.production_started_at) }}</span>
		</template>
		<template #cell(ready_at)="{ item: row }">
			<span v-if="row.ready_at" class="whitespace-nowrap" v-tooltip="row.ready_at_is_approved ? ctrans('Approved ready date') : ctrans('Ready date proposed by the supplier, not approved yet')">
				{{ useFormatTime(row.ready_at) }}<span v-if="!row.ready_at_is_approved" class="text-gray-400"> ({{ ctrans("proposed") }})</span>
			</span>
		</template>
		<template #cell(quantity)="{ item: row }">
			<span class="tabular-nums">{{ formatQuantity(row.quantity) }}</span>
		</template>
		<template #cell(skos)="{ item: row }">
			<span class="tabular-nums" v-tooltip="ctrans('1 SKO = :units units', { units: formatQuantity(row.units_per_sko) })">{{ formatQuantity(row.skos) }}</span>
		</template>
	</Table>
</template>

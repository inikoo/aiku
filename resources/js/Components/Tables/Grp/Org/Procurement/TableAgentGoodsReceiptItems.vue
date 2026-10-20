<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-17 09:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faClipboardCheck, faImage } from "@fal"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

defineProps<{
	data: {}
	tab?: string
}>()

const emit = defineEmits<{ check: [item: any] }>()

const locale = useLocaleStore()
const organisation = route().params["organisation"]
const purchaseOrderRoute = (slug: string) => route("grp.org.agent.purchase_orders.show", [organisation, slug])

const formatQuantity = (value: number) => locale.number(Math.round(Number(value) * 1000) / 1000)
const skos = (row: any) => formatQuantity(row.quantity / (row.units_per_sko || 1))

const itemStatus = (item: any) => {
	if (!item.has_been_checked) {
		return { label: ctrans("Not checked yet"), class: "bg-amber-50 text-amber-700 ring-amber-200" }
	}
	if (Number(item.quantity_rejected) > 0) {
		return { label: ctrans(":units rejected", { units: formatQuantity(item.quantity_rejected ?? 0) }), class: "bg-red-50 text-red-700 ring-red-200" }
	}

	return { label: ctrans("No rejects"), class: "bg-green-50 text-green-700 ring-green-200" }
}
</script>

<template>
	<Table :resource="data" :name="tab">
		<template #cell(code)="{ item: row }">
			<span class="whitespace-nowrap font-medium text-gray-800">{{ row.code }}</span>
		</template>
		<template #cell(purchase_order_reference)="{ item: row }">
			<Link v-if="row.purchase_order_slug" :href="purchaseOrderRoute(row.purchase_order_slug)" class="secondaryLink whitespace-nowrap">{{ row.purchase_order_reference }}</Link>
		</template>
		<template #cell(quantity_ordered)="{ item: row }">
			<span class="tabular-nums text-gray-500">{{ formatQuantity(row.quantity_ordered) }}</span>
		</template>
		<template #cell(quantity)="{ item: row }">
			<span class="tabular-nums" v-tooltip="ctrans(':skos SKO', { skos: skos(row) })">{{ formatQuantity(row.quantity) }}</span>
		</template>
		<template #cell(status)="{ item: row }">
			<div class="text-xs">
				<span class="whitespace-nowrap rounded-full px-2 py-0.5 ring-1 ring-inset" :class="itemStatus(row).class">{{ itemStatus(row).label }}</span>
				<span v-if="row.is_packed" class="ml-1 whitespace-nowrap text-gray-500">{{ ctrans("Packed for a container") }}</span>
				<div v-if="row.reject_reason" class="mt-1 whitespace-pre-line text-gray-600">{{ row.reject_reason }}</div>
				<div v-if="row.photos.length" class="mt-1 flex flex-wrap gap-2">
					<a v-for="photo in row.photos" :key="photo.id" :href="photo.url" target="_blank" rel="noopener" class="secondaryLink inline-flex items-center gap-1">
						<FontAwesomeIcon :icon="faImage" fixed-width aria-hidden="true" />
						{{ photo.name }}
					</a>
				</div>
				<div v-if="row.checked_by" class="mt-0.5 text-gray-400">{{ ctrans("Checked by :name", { name: row.checked_by }) }}</div>
			</div>
		</template>
		<template #cell(action)="{ item: row }">
			<div class="text-right">
				<Button v-if="row.check_route" type="tertiary" size="xs" :label="row.has_been_checked ? ctrans('Check again') : ctrans('Check')" @click="emit('check', row)">
					<template #icon>
						<FontAwesomeIcon :icon="faClipboardCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Table>
</template>

<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faTimesCircle, faCamera, faExclamationTriangle } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Table from "@/Components/Table/Table.vue"
import Icon from "@/Components/Icon.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"

library.add(faCheckCircle, faTimesCircle)

defineProps<{
	data: {}
	tab?: string
}>()

const locale = useLocaleStore()
const organisation = route().params["organisation"]
const receiptRoute = (slug: string) => route("grp.org.agent.warehouse.received.goods_receipts.show", [organisation, slug])

const formatQuantity = (value: number) => locale.number(Math.round(Number(value) * 1000) / 1000)
</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">
		<template #cell(state)="{ item: row }">
			<Icon :data="row.state_icon" />
		</template>
		<template #cell(reference)="{ item: row }">
			<Link :href="receiptRoute(row.slug)" class="primaryLink whitespace-nowrap" :class="{ 'line-through opacity-60': row.state === 'cancelled' }">{{ row.reference }}</Link>
		</template>
		<template #cell(received_at)="{ item: row }">
			<span class="whitespace-nowrap">{{ useFormatTime(row.received_at) }}</span>
		</template>
		<template #cell(supplier_code)="{ item: row }">
			<span class="whitespace-nowrap" v-tooltip="row.supplier_name">{{ row.supplier_code }}</span>
		</template>
		<template #cell(supplier_delivery_note)="{ item: row }">
			<span v-if="row.supplier_delivery_note" class="whitespace-nowrap">{{ row.supplier_delivery_note }}</span>
		</template>
		<template #cell(number_photos)="{ item: row }">
			<span v-if="row.number_photos" class="inline-flex items-center gap-1 text-green-600" v-tooltip="ctrans(':count photos of the signed delivery note', { count: row.number_photos })">
				<FontAwesomeIcon :icon="faCamera" fixed-width aria-hidden="true" />
				<span class="tabular-nums">{{ row.number_photos }}</span>
			</span>
			<span v-else-if="row.state !== 'cancelled'" class="inline-flex items-center gap-1 whitespace-nowrap text-xs text-amber-600">
				<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
				{{ ctrans("Not uploaded") }}
			</span>
		</template>
		<template #cell(number_items)="{ item: row }">
			<span class="tabular-nums">{{ locale.number(row.number_items) }}</span>
		</template>
		<template #cell(quantity)="{ item: row }">
			<span class="tabular-nums">{{ formatQuantity(row.quantity) }}</span>
		</template>
	</Table>
</template>

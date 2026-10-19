<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheckCircle, faExclamationTriangle, faFileExcel, faPeopleCarry } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faCheckCircle, faExclamationTriangle, faFileExcel, faPeopleCarry)

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	currency_code: string
	clients: { id: number; code: string }[]
	data: object
}>()

const locale = useLocaleStore()
const money = (amount: number) => locale.currencyFormat(props.currency_code, amount)
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />

	<p class="mx-4 mt-4 max-w-4xl text-sm text-gray-600">
		{{ ctrans("Factories with open orders from more than one client. Talk to the factory once with the combined volume; the combined sheet keeps each client's cartons, carton marks and invoice apart.") }}
	</p>

	<Table :resource="data" class="mt-3">
		<template #cell(code)="{ item }">
			<div class="whitespace-nowrap font-medium text-gray-800">{{ item.code }}</div>
			<div class="text-xs text-gray-500">{{ item.name }}</div>
		</template>

		<template v-for="client in clients" :key="client.id" #[`cell(client_${client.id})`]="{ item }">
			<span class="whitespace-nowrap" :class="item[`client_${client.id}`] ? 'text-gray-800' : 'text-gray-300'">
				{{ item[`client_${client.id}`] ? money(item[`client_${client.id}`]) : "-" }}
			</span>
		</template>

		<template #cell(total)="{ item }">
			<span class="whitespace-nowrap font-semibold text-gray-800">{{ money(item.total) }}</span>
		</template>

		<template #cell(moq)="{ item }">
			<span v-if="!item.products_with_minimum" class="text-gray-400">-</span>
			<span v-else-if="item.products_below_minimum" class="inline-flex items-center gap-1 whitespace-nowrap text-amber-700">
				<FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
				{{ ctrans(":count of :total products below it", { count: item.products_below_minimum, total: item.products_with_minimum }) }}
			</span>
			<span v-else class="inline-flex items-center gap-1 whitespace-nowrap text-green-700">
				<FontAwesomeIcon icon="fal fa-check-circle" fixed-width aria-hidden="true" />
				{{ ctrans("Met") }}
			</span>
		</template>

		<template #cell(orders)="{ item }">
			<div class="flex flex-wrap gap-1">
				<Link
					v-for="order in item.orders"
					:key="order.reference"
					:href="route(order.route.name, order.route.parameters)"
					v-tooltip="`${order.reference} · ${order.state_label} · ${useFormatTime(order.date)}`"
					class="inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">
					<span class="font-semibold uppercase text-gray-700">{{ order.client_code }}</span>
					<span>{{ order.agent_order_reference }}</span>
					<span class="text-gray-400">{{ money(order.amount) }}</span>
				</Link>
			</div>
		</template>

		<template #cell(actions)="{ item }">
			<a :href="route(item.sheet_route.name, item.sheet_route.parameters)" v-tooltip="ctrans('Combined supplier sheet (Excel)')" class="whitespace-nowrap text-gray-500 hover:text-gray-800">
				<FontAwesomeIcon icon="fal fa-file-excel" fixed-width aria-hidden="true" />
				<span class="ml-1 text-xs">{{ ctrans("Combined sheet") }}</span>
			</a>
		</template>
	</Table>
</template>

<!--
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle } from "@fal"

library.add(faExclamationTriangle)

interface Row {
	code: string
	name: string
	party_type: "Supplier" | "Agent"
	incidents: number
	open: number
	disputed: number
	recovered: number
	last_incident_at: string | null
	route: routeType | null
}

defineProps<{
	title: string
	pageHead: PageHeadingTypes
	data: {}
	currency: string
}>()

const locale = useLocaleStore()
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />
	<Table :resource="data">
		<template #cell(code)="{ item }: { item: Row }">
			<Link v-if="item.route" :href="route(item.route.name, item.route.parameters) + '?tab=notes'" class="primaryLink">{{ item.code }}</Link>
			<span v-else>{{ item.code }}</span>
			<span v-if="item.party_type === 'Agent'" class="ml-1 text-xs text-gray-500">{{ ctrans("Agent") }}</span>
		</template>
		<template #cell(open)="{ item }: { item: Row }">
			<span class="tabular-nums" :class="item.open ? 'font-semibold text-gray-900' : 'text-gray-400'">{{ item.open }}</span>
		</template>
		<template #cell(disputed)="{ item }: { item: Row }">
			<span class="tabular-nums">{{ locale.currencyFormat(currency, item.disputed) }}</span>
		</template>
		<template #cell(recovered)="{ item }: { item: Row }">
			<span class="tabular-nums" :class="item.recovered ? 'text-green-600' : 'text-gray-400'">{{ locale.currencyFormat(currency, item.recovered) }}</span>
		</template>
		<template #cell(last_incident_at)="{ item }: { item: Row }">
			{{ item.last_incident_at ? useFormatTime(item.last_incident_at) : "" }}
		</template>
	</Table>
</template>

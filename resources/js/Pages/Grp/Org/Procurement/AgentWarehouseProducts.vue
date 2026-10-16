<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-15 15:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref, watch } from "vue"
import { Head, router } from "@inertiajs/vue3"
import qs from "qs"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faIndustryAlt, faInboxIn, faInfoCircle } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableAgentWarehouseProducts from "@/Components/Tables/Grp/Org/Procurement/TableAgentWarehouseProducts.vue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faIndustryAlt, faInboxIn)

type FilterOption = { label: string; value: string; count: number }

const props = defineProps<{
	pageHead: PageHeadingTypes
	title: string
	kind: "in_production" | "received"
	data: object
	filterOptions: { organisation: FilterOption[]; location?: FilterOption[] }
}>()

const currentQuery = () => qs.parse(location.search.substring(1)) as Record<string, any>
const organisation = ref<string | null>(currentQuery().filter?.organisation ?? null)
const where = ref<string | null>(currentQuery().filter?.location ?? null)

watch([organisation, where], () => {
	const query = currentQuery()
	query.filter = { ...(query.filter ?? {}), organisation: organisation.value || undefined, location: where.value || undefined }
	delete query.page
	router.get(`${location.pathname}?${qs.stringify(query, { skipNulls: true })}`, {}, { preserveState: true, preserveScroll: true, replace: true })
})
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />
	<div class="mx-4 mt-3 flex gap-2 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] p-3 text-sm text-gray-700">
		<FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-[--app-accent]" fixed-width aria-hidden="true" />
		<p v-if="props.kind === 'in_production'">
			{{ ctrans("Products the suppliers are making now: the purchase orders placed through us with production started and the quality check not passed yet. Each line shows what the supplier still owes: ordered, less what we received and kept; rejected units count as owed again and stay here until their replacements arrive and pass the check, also after the order's quality check. Quantities are in units; the SKO column shows the same quantity in packs.") }}
		</p>
		<p v-else>
			{{ ctrans("Everything we received from the suppliers and still hold: in our warehouse, packed or not, and in containers that have not been dispatched yet. Quantities are in units; the SKO column shows the same quantity in packs.") }}
		</p>
	</div>
	<div class="flex flex-wrap gap-x-4 gap-y-2 px-4 pt-3">
		<PillFilterBar :label="ctrans('AW company')" :options="filterOptions.organisation" :selected="organisation" @select="(value) => (organisation = value)" />
		<PillFilterBar v-if="filterOptions.location" :label="ctrans('Where')" :options="filterOptions.location" :selected="where" @select="(value) => (where = value)" />
	</div>
	<TableAgentWarehouseProducts :data="data" :kind="kind" />
</template>

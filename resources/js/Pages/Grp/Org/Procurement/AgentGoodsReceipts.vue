<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref, watch } from "vue"
import { Head, router } from "@inertiajs/vue3"
import qs from "qs"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClipboardCheck, faInfoCircle } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import AgentReceiveGoodsButton from "@/Components/Procurement/AgentReceiveGoodsButton.vue"
import TableAgentGoodsReceipts from "@/Components/Tables/Grp/Org/Procurement/TableAgentGoodsReceipts.vue"
import PillFilterBar from "@/Components/Utils/PillFilterBar.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faClipboardCheck)

type FilterOption = { label: string; value: string; count: number; tooltip?: string }
type ScopeOption = { value: number; label: string; count: number }

defineProps<{
	pageHead: PageHeadingTypes
	title: string
	data: object
	filterOptions: { state: FilterOption[]; photo: FilterOption[] }
	receive?: { suppliers: ScopeOption[]; purchase_orders: ScopeOption[] } | null
}>()

const currentQuery = () => qs.parse(location.search.substring(1)) as Record<string, any>
const state = ref<string | null>(currentQuery().filter?.state ?? null)
const photo = ref<string | null>(currentQuery().filter?.photo ?? null)

watch([state, photo], () => {
	const query = currentQuery()
	query.filter = { ...(query.filter ?? {}), state: state.value || undefined, photo: photo.value || undefined }
	delete query.page
	router.get(`${location.pathname}?${qs.stringify(query, { skipNulls: true })}`, {}, { preserveState: true, preserveScroll: true, replace: true })
})
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template v-if="receive" #other>
			<AgentReceiveGoodsButton :scopes="receive" />
		</template>
	</PageHeading>
	<div class="mx-4 mt-3 flex gap-2 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] p-3 text-sm text-gray-700">
		<FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-[--app-accent]" fixed-width aria-hidden="true" />
		<p>{{ ctrans("Every delivery we received from a supplier, with the purchase order lines it brought and the photo of its signed delivery note. Use Receive goods when a supplier delivers; open a goods receipt to add its photo or cancel it.") }}</p>
	</div>
	<div class="flex flex-wrap gap-x-4 gap-y-2 px-4 pt-3">
		<PillFilterBar :label="ctrans('State')" :options="filterOptions.state" :selected="state" @select="(value) => (state = value)" />
		<PillFilterBar :label="ctrans('Delivery note photo')" :options="filterOptions.photo" :selected="photo" @select="(value) => (photo = value)" />
	</div>
	<TableAgentGoodsReceipts :data="data" />
</template>

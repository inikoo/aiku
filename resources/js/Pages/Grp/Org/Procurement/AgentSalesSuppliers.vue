<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableAgentSalesSuppliers from "@/Components/Tables/Grp/Org/Procurement/TableAgentSalesSuppliers.vue"
import AgentSalesValuation, { Valuation } from "@/Components/Procurement/AgentSalesValuation.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"

defineProps<{
	pageHead: PageHeadingTypes
	title: string
	currency: string
	valuation: Valuation
	data: object
}>()
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />
	<AgentSalesValuation :valuation="valuation" class="px-4 pt-4" />
	<TableAgentSalesSuppliers :data="data" :currency="currency" />
</template>

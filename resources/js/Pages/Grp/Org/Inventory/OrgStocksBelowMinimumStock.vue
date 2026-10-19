<!--
  -  Author: Artha <artha@aw-advantage.com>
  -  Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
  -  Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableOrgStocksMinimumStock from "@/Components/Tables/Grp/Org/Inventory/TableOrgStocksMinimumStock.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArrowToBottom, faSlidersH } from "@fal"

library.add(faArrowToBottom, faSlidersH)

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    orgStocks: {}
    canEdit: boolean
}>()
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <TableOrgStocksMinimumStock :data="orgStocks" :canEdit="canEdit" tab="minimum_stocks" class="mt-5" />
</template>

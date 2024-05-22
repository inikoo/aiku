<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 16 May 2024 12:23:59 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableSheets from "@/Components/Tables/Grp/Org/HumanResources/TableTimesheets.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"

defineProps<{
    pageHead: PageHeadingTypes
    title: string
    data: {}
}>()
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableSheets :data="data" />
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 19 Jan 2024 11:02:47 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableMarketplaceAgents from "@/Components/Tables/TableMarketplaceAgents.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps <{
    pageHead: object
    title: string
    data:object
}>()
</script>


<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableMarketplaceAgents :data="data" />
</template>


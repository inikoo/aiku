
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 24 Mar 2024 21:06:12 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableTradeUnits from "@/Components/Tables/Grp/Goods/TableTradeUnits.vue";
import { capitalize } from "@/Composables/capitalize"

defineProps<{
    data: object
    title: string
    pageHead: object
}>()


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableTradeUnits :data="data"/>
</template>


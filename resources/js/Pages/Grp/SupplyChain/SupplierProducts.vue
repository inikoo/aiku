<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 13 Sept 2024 15:27:43 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableSupplierProducts from "@/Components/Tables/Grp/SupplyChain/TableSupplierProducts.vue";
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as TSPageHeading } from "@/types/PageHeading";

const props = defineProps <{
    pageHead: TSPageHeading
    title: string
    data:object
}>()
</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableSupplierProducts :data="data" />
</template>


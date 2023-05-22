<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TablePurchaseOrders from "@/Pages/Tables/TablePurchaseOrders.vue";

const props = defineProps <{
    pageHead: object
    title: string
    data:object
}>()
</script>

<template layout="App">
    <Head :title="title"/>
    <!-- {{ pageHead }} -->
    <PageHeading :data="pageHead"></PageHeading>
    <TablePurchaseOrders :data="data" />
</template>


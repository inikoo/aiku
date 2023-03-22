<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableSuppliers from "@/Pages/Tables/TableSuppliers.vue";

const props = defineProps <{
    pageHead: object
    tittle: string
    data: object
}>()

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableSuppliers :data="data" />
</template>


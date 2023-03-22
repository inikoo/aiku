<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableDispatchedEmails from "@/Pages/Tables/TableDispatchedEmails.vue";

const props = defineProps<{
    data: object
    tittle: string
    pageHead: object
}>()



</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableDispatchedEmails :data="data"/>
</template>


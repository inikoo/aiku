<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableProspects from "@/Components/Tables/Grp/Org/CRM/TableProspects.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()


</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableProspects :data="data" />
</template>


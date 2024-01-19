<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableProspects from "@/Components/Tables/TableProspects.vue";
import { capitalize } from "@/Composables/capitalize"
import TableOrganisations from "@/Components/Tables/TableOrganisations.vue";

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableOrganisations :data="data" />
</template>

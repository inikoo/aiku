<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableAgents from "@/Components/Tables/Grp/Org/Procurement/TableOrgAgents.vue";
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
    <TableAgents :data="data" />
</template>


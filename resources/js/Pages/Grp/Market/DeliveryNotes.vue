<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableDeliveryNotes from "@/Components/Tables/TableDeliveryNotes.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps<{
  pageHead: object
  data: object
  title: string
}>()

</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableDeliveryNotes :data="data" />
</template>


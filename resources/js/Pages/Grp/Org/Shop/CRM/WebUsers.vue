<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Feb 2024 02:24:05 Malaysia Time, Madrid Spain
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableWebUsers from "@/Components/Tables/Grp/Org/CRM/TableWebUsers.vue";
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
    <TableWebUsers :data="data" />
</template>


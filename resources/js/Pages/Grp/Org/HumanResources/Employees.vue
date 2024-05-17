<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 16 May 2024 12:23:41 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableEmployees from "@/Components/Tables/Grp/Org/HumanResources/TableEmployees.vue";
import { capitalize } from "@/Composables/capitalize"
import "@/Composables/Icon/EmployeeStateIcon.ts"

const props = defineProps <{
    pageHead: object
    title: string
    data: object
}>()
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableEmployees :data="data" />
</template>


<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableEmployees from "@/Components/Tables/TableEmployees.vue";
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


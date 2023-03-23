<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableEmployees from "@/Pages/Tables/TableEmployees.vue";

const props = defineProps <{
    pageHead: object
    tittle: string
    data: object
}>()
</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableEmployees :data="data" />
</template>


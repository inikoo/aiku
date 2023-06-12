<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableWorkingPlaces from "@/Pages/Tables/TableWorkingPlaces.vue";

const props = defineProps <{
    pageHead: object
    title: string
    data: object
}>()
</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableWorkingPlaces :data="data" />
</template>


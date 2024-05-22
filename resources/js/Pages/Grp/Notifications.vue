<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import Table from '@/Components/Table/Table.vue'


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    data: {}
}>()

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <Table :resource="data" class="mt-5">

    </Table>

</template>
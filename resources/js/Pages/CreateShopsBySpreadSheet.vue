
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize";
import Edittable from "@/Components/EditableTable.vue";
const props = defineProps<{
    pageHead: object
    title: string
    data: object
}>()
console.log('test',props)
</script>
  
<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Edittable :data="{ columns: props.data['fields'], value: [], theme: 'compact' }"></Edittable>
</template> 
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'

import { capitalize } from "@/Composables/capitalize"
import { ref } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'

// import FileShowcase from '@/xxxxxxxxxxxx'

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    data: {}
    
}>()



</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    {{ data }}
xx
</template>
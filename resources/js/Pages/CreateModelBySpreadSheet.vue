<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize";
import SpreadSheet from "@/Components/SpreadSheets/SpreadSheet.vue";

const props = defineProps<{
    pageHead: object
    title: string
    sheetData: {

        columns: Array<{
            id: string
            name: string
            type: string
            prop: string
            required?: boolean
        }>
    }
    saveRoute:{
        name:string,
        arguments?:Array<string>
    }
}>();
console.log("test", props);
</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <SpreadSheet
        :data="{ columns: props.sheetData.columns, value: [] }"
        :theme="'compact'"
        :actionRoute="saveRoute"
    ></SpreadSheet>
</template>

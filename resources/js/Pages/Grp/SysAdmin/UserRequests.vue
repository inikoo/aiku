<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import  PageHeading from '@/Components/Headings/PageHeading.vue'
import TableGuests from "@/Components/Tables/Grp/SysAdmin/TableGuests.vue";
import { capitalize } from "@/Composables/capitalize"
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue";

const props = defineProps <{
    data: object
    title: string
    pageHead: object
}>()

</script>
<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableUserRequestLogs :data="data" />

</template>
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 23 Feb 2024 09:56:34 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import TablePalletDeliveries from "@/Components/Tables/Retina/Storage/TablePalletDeliveries.vue";

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

</script>

<template layout="Retina">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TablePalletDeliveries :data="data" />
</template>

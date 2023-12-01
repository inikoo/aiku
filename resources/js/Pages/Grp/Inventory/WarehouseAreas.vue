<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 20:33:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableWarehouseAreas from "@/Pages/Tables/TableWarehouseAreas.vue";
import { capitalize } from "@/Composables/capitalize"


const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableWarehouseAreas :data="data"/>
</template>


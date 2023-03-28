<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 24 Oct 2022 22:46:45 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import TableStockFamilies from "@/Pages/Tables/TableStockFamilies.vue";

const props = defineProps<{
    data: object
    tittle: string
    pageHead: object
}>()

const locale = useLocaleStore();

const columnsType = {
    'number_stocks': 'number'
}


</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TableStockFamilies :data="data" />
</template>


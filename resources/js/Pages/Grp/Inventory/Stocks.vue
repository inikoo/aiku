<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:55:18 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import TableStocks from "@/Components/Tables/TableStocks.vue";
import { capitalize } from "@/Composables/capitalize"

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

const locale = useLocaleStore();

const columnsType={
    'quantity':'number'
}

</script>


<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableStocks :data="data"/>
</template>


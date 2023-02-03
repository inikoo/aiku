<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 24 Oct 2022 22:46:45 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';
import {useLocaleStore} from '@/Stores/locale.js';

const props = defineProps(['stockFamilies', 'title', 'pageHead']);
const locale = useLocaleStore();


const columnsType = {
    'number_stocks': 'number'
}


</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="stockFamilies" :columnsType=columnsType class="mt-5">
        <template #cell(code)="{ item: stockFamily }">
            <Link :href="route('inventory.stock-families.show',stockFamily.slug)">
                {{ stockFamily.code }}
            </Link>
        </template>
        <template #cell(number_stocks)="{ item: stockFamily }">
            {{ locale.number(stockFamily['number_stocks']) }}
        </template>

    </Table>
</template>


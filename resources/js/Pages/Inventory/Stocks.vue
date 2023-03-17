<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:55:18 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';
import {useLocaleStore} from '@/Stores/locale.js';

const props =defineProps(['stocks', 'title','pageHead']);
const locale = useLocaleStore();


const columnsType={
    'quantity':'number'
}



</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="stocks"  :columnsType=columnsType  class="mt-5">
        <template #cell(code)="{ item: stock }">
            <Link :href="route('inventory.stocks.show',stock.slug)">
                {{ stock.code }}
            </Link>
        </template>
        <template #cell(quantity)="{ item: stock }" >
          {{locale.number(stock['quantity'])}}
        </template>

    </Table>
</template>


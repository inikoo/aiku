<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['customers', 'title','pageHead']);

const itemRoute = route().current().replace(/index$/i, 'show')

function routeParameters(customer) {
    switch (route().current()) {
        case 'shops.show.customers.index':
            return [customer['shop_slug'],customer.slug]

        default:
            return [customer.slug]
    }
}


</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="customers" class="mt-5">


        <template #cell(reference)="{ item: customer }">
            <Link :href="route(itemRoute,routeParameters(customer))">
                {{ customer['reference'] }}
            </Link>
        </template>
        <template #cell(shop)="{ item: customer }">
            <Link :href="route('shops.show.customers.index',customer['shop_slug'])">
                {{ customer['shop'] }}
            </Link>

        </template>

    </Table>
</template>


<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Customer} from "@/types/customer";

const props = defineProps<{
    data: object
}>()


function customerRoute(customer: Customer) {
    switch (route().current()) {
        case 'shops.show.customers.index':
            return route(
                'shops.show.customers.show',
                [customer.shop_slug, customer.slug]);
        default:
            return route(
                'customers.show',
                [customer.slug]);
    }
}

function shopRoute(customer: Customer) {
    switch (route().current()) {
        case 'shops.show.customers.index':
            return route(
                'shops.show',
                [customer.shop_slug]);
        default:
            return route(
                'shops.show',
                [customer.shop_slug]);
    }
}


</script>

<template>
    <Table :resource="data" :name="'cus'" class="mt-5">
        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)">
                {{ customer['reference'] }}
            </Link>
        </template>
        <template #cell(shop)="{ item: customer }">
            <Link :href="shopRoute(customer)">
                {{ customer['shop'] }}
            </Link>
        </template>
    </Table>
</template>



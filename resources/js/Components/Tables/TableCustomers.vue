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
    data: object,
    tab?: string
}>()


function customerRoute(customer: Customer) {
    switch (route().current()) {
        case 'shops.show.customers.index':
            return route(
                'grp.crm.shops.show.customers.show',
                [customer.shop_slug, customer.slug]);
        case 'grp.fulfilment.customers.index':
            return route(
                'grp.fulfilment.customers.show',
                [customer.slug]);
        default:
            return route(
                'grp.org.shops.show.customers.show',
                [
                    route().params['organisation'],
                    route().params['shop'],
                    customer.slug
                ]);
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
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="specialUnderline">
                {{ customer['slug'] }}
            </Link>
        </template>
        <template #cell(shop)="{ item: customer }" class="specialUnderline">
            <Link :href="shopRoute(customer)">
                {{ customer['shop'] }}
            </Link>
        </template>
    </Table>
</template>



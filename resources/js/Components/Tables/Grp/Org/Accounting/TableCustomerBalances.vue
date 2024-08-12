<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 13:45:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { FulfilmentCustomer } from "@/types/Customer";
import { useLocaleStore } from "@/Stores/locale";

const props = defineProps<{
    data: object,
    tab?: string
}>();

const locale = useLocaleStore();

function customerRoute(customer: {}) {
    switch (route().current()) {
        case 'grp.org.accounting.balances.index':
            if(customer.shop_type === 'fulfilment')
            {
                return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [route().params['organisation'], customer.fulfilment_slug, customer.slug])
            } else {
                return route(
                'grp.org.shops.show.crm.customers.show',
                [route().params['organisation'], customer.shop_slug, customer.slug])
            }
            
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(name)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="primaryLink">
                {{ customer.name }}
            </Link>
        </template>
    </Table>
</template>



<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
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
        default:
            return route(
                'grp.org.fulfilment.shops.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    customer.slug
                ]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: customer }">
            <Link :href="customerRoute(customer)">
                {{ customer['slug'] }}
            </Link>
        </template>
    </Table>
</template>



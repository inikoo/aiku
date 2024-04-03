<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { FulfilmentCustomer } from "@/types/Customer"

const props = defineProps<{
    data: object,
    tab?: string
}>()



function customerRoute(customer: FulfilmentCustomer) {
    switch (route().current()) {
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    customer.slug
                ])
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="specialUnderline">
                {{ customer['reference'] }}
            </Link>
        </template>
    </Table>
</template>

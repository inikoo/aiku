<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { FulfilmentCustomer } from "@/types/Customer"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from 'laravel-vue-i18n'
import warehouse from "@/Pages/Grp/Org/Warehouse/Warehouse.vue";
library.add(faCheck, faTimes)

const props = defineProps<{
    data: {}
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
        <template #cell(status)="{ item: customer }">
          <div v-tooltip="customer.status_icon.tooltip" class="px-1 py-0.5">
            <FontAwesomeIcon :icon='customer.status_icon.icon' :class='customer.status_icon.class' fixed-width aria-hidden='true' />
          </div>
        </template>

        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="primaryLink">
                {{ customer['reference'] }}
            </Link>
        </template>
    </Table>
</template>

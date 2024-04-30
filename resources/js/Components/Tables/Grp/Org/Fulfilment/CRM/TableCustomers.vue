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
        <template #cell(rental_agreement)="{ item: customer }">
            <div v-if="customer.rental_agreement" class="w-fit mx-auto" v-tooltip="trans('Already set rental agreement.')">
                <FontAwesomeIcon icon='fal fa-check' class='text-green-500' fixed-width aria-hidden='true' />
            </div>
            <div v-else v-tooltip="trans('Not set rental agreement yet.')">
                <FontAwesomeIcon icon='fal fa-times' class='text-red-500' fixed-width aria-hidden='true' />
            </div>
        </template>

        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="specialUnderline">
                {{ customer['reference'] }}
            </Link>
        </template>
    </Table>
</template>

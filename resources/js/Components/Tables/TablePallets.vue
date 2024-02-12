<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import Icon from '@/Components/Icon.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt } from '@far';
import { faSignOutAlt } from '@fal';
library.add(
    faTrashAlt,faSignOutAlt
)
const props = defineProps<{
    data: object,
    tab?:string
}>()


function customerRoute(pallet: Customer) {
    console.log( route().params,pallet)
    switch (route().current()) {
        case  'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.pallets.show':
            break;
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                ]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: pallet }">
            <Link :href="customerRoute(pallet)" class="specialUnderline">
                {{ pallet['reference'] }}
            </Link>
        </template>
        <template #cell(location)="{ item: pallet }">
            <AddressLocation v-if="pallet['location']" :data="pallet['location']"/>
        </template>
        <template #cell(state)="{ item: pallet }">
            <Icon :data="pallet['state_icon']" class="px-1"/>
        </template>
    </Table>
</template>

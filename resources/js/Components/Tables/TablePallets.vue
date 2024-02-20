<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link,useForm} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import Icon from '@/Components/Icon.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt } from '@far';
import { faSignOutAlt } from '@fal';
import Checkbox from '../Checkbox.vue';

library.add(
    faTrashAlt,faSignOutAlt
)
const props = defineProps<{
    data: object,
    tab?:string
    form: object
}>()


function customerRoute(pallet: Customer) {
    switch (route().current()) {
        case  'grp.org.warehouses.show.fulfilment.pallets.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    pallet['id']
                ]);

        case  'grp.org.warehouses.show.infrastructure.locations.show':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    route().params['location'],
                    pallet['id']
                ]);

        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet['id']
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
    <!--     <template #cell(actions)="{ item: actions }">
        <div> 
            <input type="checkbox" :id="actions.id"  :value="actions.id" v-model="form.pallet"  
            class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600">
        </div>
        </template> -->
    </Table>
</template>

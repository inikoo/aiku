<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import Icon from '@/Components/Icon.vue'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt } from '@far';
import { faSignOutAlt } from '@fal';
import Tag from "@/Components/Tag.vue"

library.add(
    faTrashAlt,faSignOutAlt
)
const props = defineProps<{
    data: object,
    tab?:string
}>()


function palletRoute(pallet: Pallet) {
    switch (route().current()) {
        case  'grp.org.fulfilments.show.operations.pallets.index':
            return route(
                'grp.org.fulfilments.show.operations.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    pallet['reference']
                ]);
        case  'grp.org.warehouses.show.fulfilment.pallets.index':
            return route(
                'grp.org.warehouses.show.fulfilment.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    pallet['reference']
                ]);

        case  'grp.org.warehouses.show.infrastructure.locations.show':
            return route(
                'grp.org.warehouses.show.infrastructure.locations.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['warehouse'],
                    route().params['location'],
                    pallet['reference']
                ]);
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['fulfilmentCustomer'],
                    pallet['reference']
                ]);

        default:
            return [];
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: pallet }">
            <Link :href="palletRoute(pallet)" class="specialUnderline">
                {{ pallet['reference'] }}
            </Link>
        </template>

        <template #cell(state)="{ item: pallet }">
            <Icon :data="pallet['state_icon']" class="px-1"/>
        </template>
        <template #cell(stored_items)="{ item: pallet }">
            <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                <Tag v-for="item of pallet.stored_items" :theme="item.id" :label="`${item.reference} (${item.quantity})`" :closeButton="false"
                    :stringToColor="true">
                    <template #label>
                        <div class="whitespace-nowrap text-xs">
                            {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                        </div>
                    </template>
                </Tag>
            </div>
            <div v-else class="text-gray-400 text-xs italic">
                No items in this pallet
            </div>

        </template>
    </Table>
</template>

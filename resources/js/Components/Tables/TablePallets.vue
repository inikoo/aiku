<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { MarketplaceAgent } from "@/types/marketplace-agent";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import ProcurementMarketplaceAdoption from "@/Components/Elements/Specialised/ProcurementMarketplaceAdoption.vue";

const props = defineProps<{
    data: object,
    tab?:string
}>()


function customerRoute(pallet: Customer) {
    switch (route().current()) {
        default:
            return route(
                'grp.org.warehouses.show.fulfilment.pallets.show',
                [
                    route().params['organisation'],
                    pallet.warehouse_slug,
                    pallet.slug
                ]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: pallet }">
            <Link :href="customerRoute(pallet)" class="specialUnderline">
                {{ pallet['slug'] }}
            </Link>
        </template>
        <template #cell(location)="{ item: pallet }">
            <AddressLocation v-if="pallet['location']" :data="pallet['location']"/>
        </template>
    </Table>
</template>

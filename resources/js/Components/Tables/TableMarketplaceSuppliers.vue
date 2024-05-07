<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { MarketplaceSupplier } from "@/types/marketplace-supplier";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import ProcurementMarketplaceAdoption from "@/Components/Elements/Specialised/ProcurementMarketplaceAdoption.vue";

const props = defineProps<{
    data: object
    tab?:string
}>()


/*function marketplaceSupplierRoute(marketplaceSupplier: MarketplaceSupplier) {
    switch (route().current()) {
        case 'grp.org.procurement.marketplace.agents.show':
            return route(
                'grp.org.procurement.marketplace.agents.show.suppliers.show',
                [route().params['agent'],marketplaceSupplier.slug]);
        case 'grp.org.procurement.marketplace.suppliers.index':
            return route(
                'grp.org.procurement.marketplace.suppliers.show',
                [marketplaceSupplier.slug]);
        default:
            return route(
                'grp.org.procurement.marketplace.suppliers.show',
                [marketplaceSupplier.slug]);
    }
}*/

</script>

<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(adoption)="{ item: supplier }">
            <ProcurementMarketplaceAdoption :value="supplier['adoption']"/>
        </template>
<!--        <template #cell(code)="{ item: supplier }">
            <Link :href="marketplaceSupplierRoute(supplier)">
                {{ supplier['code'] }}
            </Link>
        </template>-->
        <template #cell(location)="{ item: supplier }">
            <AddressLocation :data="supplier['location']"/>
        </template>
    </Table>
</template>



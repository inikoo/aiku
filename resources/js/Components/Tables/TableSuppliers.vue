<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Supplier} from "@/types/supplier";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import {useLocaleStore} from '@/Stores/locale';

const props = defineProps<{
    data: object,
    tab?: string
}>()

const locale = useLocaleStore();

/*function supplierRoute(supplier: Supplier) {
    switch (route().current()) {
        case 'grp.procurement.suppliers.index':
            return route(
                'grp.procurement.suppliers.show',
                [supplier.slug]);
        default:
            return route(
                'grp.org.procurement.agents.show.suppliers.show',
                [
                    supplier.org_slug,
                    supplier.agent_slug,
                    supplier.slug
                ]);
    }
}*/

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
<!--        <template #cell(code)="{ item: supplier }">-->
<!--            <Link :href="supplierRoute(supplier)">
                {{ supplier['code'] }}
            </Link>-->
<!--        </template>-->
<!--        <template #cell(supplier_locations)="{ item: supplier }">
            <AddressLocation :data="supplier['supplier_locations']"/>
        </template>-->
        <template #cell(number_supplier_products)="{ item: supplier }">
            {{locale.number(supplier.number_supplier_products)}}
        </template>
    </Table>
</template>



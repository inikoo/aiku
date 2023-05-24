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

const props = defineProps<{
    data: object
}>()


function supplierRoute(supplier: Supplier) {
    switch (route().current()) {
        case 'procurement.suppliers.index':
            return route(
                'procurement.suppliers.show',
                [supplier.slug]);
        default:
            return route(
                'procurement.agents.show.suppliers.show',
                [
                    supplier.agent_slug,
                    supplier.slug
                ]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'su'" class="mt-5">
        <template #cell(code)="{ item: supplier }">
            <Link :href="supplierRoute(supplier)">
                {{ supplier['code'] }}
            </Link>
        </template>
        <template #cell(supplier_locations)="{ item: supplier }">
            <AddressLocation :data="supplier['supplier_locations']"/>
        </template>
    </Table>
</template>



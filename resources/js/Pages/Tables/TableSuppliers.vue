<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Supplier} from "@/types/supplier";

const props = defineProps<{
    data: object
}>()


function supplierRoute(supplier: Supplier) {
    switch (route().current()) {
        case 'procurement.suppliers.index':
            return route(
                'procurement.suppliers.show',
                [supplier.name, supplier.email]);
        default:
            return route(
                'suppliers.show',
                [supplier.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'as'" class="mt-5">
        <template #cell(code)="{ item: supplier }">
            <Link :href="route(supplierRoute(supplier))">
                {{ supplier.code }}
            </Link>
        </template>
    </Table>
</template>



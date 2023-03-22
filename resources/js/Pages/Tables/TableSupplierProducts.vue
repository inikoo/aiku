<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {SupplierProduct} from "@/types/supplier-product";

const props = defineProps<{
    data: object
}>()


function supplierProductRoute(supplierProduct: SupplierProduct) {
    switch (route().current()) {
        case 'procurement.supplierProducts.index':
            return route(
                'procurement.supplierProducts.show',
                [supplierProduct.slug, supplierProduct.slug]);
        default:
            return route(
                'supplierProducts.show',
                [supplierProduct.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'sp'" class="mt-5">
        <template #cell(code)="{ item: supplier_product }">
            <Link :href="route(supplierProductRoute(supplier_product))">
                {{ supplier_product.code }}
            </Link>
        </template>
    </Table>
</template>



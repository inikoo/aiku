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
    data: object,
    tab?: string
}>()


function supplierProductRoute(supplierProduct: SupplierProduct) {
    // console.log(route().current())
    switch (route().current()) {
        case 'grp.org.procurement.suppliers.show':
            return route(
                'grp.org.procurement.suppliers.show.supplier_products.show',
                [supplierProduct.supplier_slug, supplierProduct.slug])
        case 'grp.org.procurement.agents.show':
            return route(
                'grp.org.procurement.agents.show.supplier_products.show',
                [supplierProduct.agent_slug, supplierProduct.slug]);
        case 'grp.org.procurement.agents.show.suppliers.show':
            return route(
                'grp.org.procurement.agents.show.suppliers.show.supplier_products.show',
                [supplierProduct.agent_slug, supplierProduct.supplier_slug, supplierProduct.slug]);
        default:
            return route(
                'grp.org.procurement.org_supplier_products.show',
                [supplierProduct.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: supplier_product }">
            <Link :href="supplierProductRoute(supplier_product)">
                {{ supplier_product['slug'] }}
            </Link>
        </template>
    </Table>
</template>



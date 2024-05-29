<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 19:51:16 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { SupplierProduct } from "@/types/supplier-product";

const props = defineProps<{
    data: object
    tab?:string
}>();


function supplierProductRoute(supplierProduct: SupplierProduct) {
    switch (route().current()) {
        case "procurement.marketplace.agents.show":
            return route(
                "procurement.marketplace.agents.show.supplier_products.show",
                [supplierProduct.agent_slug, supplierProduct.slug]);
        case "procurement.marketplace.agents.show.suppliers.show":
            return route(
                "procurement.marketplace.agents.show.suppliers.show.supplier_products.show",
                [supplierProduct.agent_slug, supplierProduct.supplier_slug, supplierProduct.slug]);
        case "procurement.marketplace.suppliers.show":
            return route(
                "procurement.marketplace.suppliers.show.supplier_products.show",
                [supplierProduct.supplier_slug, supplierProduct.slug]);
        default:
            return route(
                "procurement.marketplace.supplier_products.show",
                [supplierProduct.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: supplier_product }">
            <Link :href="supplierProductRoute(supplier_product)">
                {{ supplier_product["code"] }}
            </Link>
        </template>
    </Table>
</template>



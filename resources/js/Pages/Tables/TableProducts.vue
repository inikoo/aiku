<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Product} from "@/types/product";

const props = defineProps<{
    data: object
}>()


function productRoute(product: Product) {
    switch (route().current()) {
        case 'catalogue.shop.hub.products.index':
            return route(
                'catalogue.shop.products.show',
                [route().params['shop'], product.slug]);
        default:
            return route(
                'catalogue.products.show',
                [product.slug]);
    }
}



</script>

<template>
    <Table :resource="data" :name="'prod'" class="mt-5">
        <template #cell(code)="{ item: product }">
            <Link :href="productRoute(product)">
                {{ product['code'] }}
            </Link>
        </template>
    </Table>
</template>



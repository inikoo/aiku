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
    tab?:string,
}>()


function productRoute(product: Product) {
    switch (route().current()) {
        case 'shops.show':
        case "shops.show.products.index":
            return route(
                'shops.show.products.show',
                [route().params['shop'], product.slug]);
        default:
            return route(
                'shops.show.products.show',
                [product.shop_slug,product.slug]);
    }
}



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: product }">
            <Link :href="productRoute(product)">
                {{ product['slug'] }}
            </Link>
        </template>
    </Table>
</template>



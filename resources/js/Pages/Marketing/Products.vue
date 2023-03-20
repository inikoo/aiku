<!--
  -  Author: Jonathan Lopez <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Jonathan Lopez
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['products', 'title', 'pageHead']);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="products" class="mt-5">
        <template #cell(code)="{ item: product }">
            <Link :href="route('shops.show.products.show',[product.shop_slug,product.slug])">
                {{ product.code }}
            </Link>
        </template>
    </Table>
</template>


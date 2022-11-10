
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head, Link} from '@inertiajs/inertia-vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['shops', 'title','pageHead']);

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="shops" class="mt-5">


        <template #cell(code)="{ item: shop }">
            <Link :href="route('shops.show',shop.slug)">
                {{ shop.code }}
            </Link>
        </template>

    </Table>
</template>


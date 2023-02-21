<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['delivery_notes', 'title', 'pageHead']);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="delivery_notes" class="mt-5">


        <template #cell(code)="{ item: delivery_notes }">
            <Link :href="route('shops.show.delivery_notes.show',[delivery_notes.shop_slug,delivery_notes.slug])">
                {{ delivery_notes.code }}
            </Link>
        </template>

    </Table>
</template>


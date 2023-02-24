<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['suppliers', 'title', 'pageHead']);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="suppliers" class="mt-5">


        <template #cell(code)="{ item: supplier }">
            <Link :href="route('procurement.suppliers.show',[supplier.slug])">
                {{ supplier.code }}
            </Link>
        </template>

    </Table>
</template>


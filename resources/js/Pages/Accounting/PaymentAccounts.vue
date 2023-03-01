<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['payment_accounts', 'title', 'pageHead']);

</script>

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="payment_accounts" class="mt-5">


        <template #cell(code)="{ item: payment_account }">
            <Link :href="route('accounting.accounts.index',[payment_account.slug])">
                {{ payment_account.code }}
            </Link>
        </template>

    </Table>
</template>


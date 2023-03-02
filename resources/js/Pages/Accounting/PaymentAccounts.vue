<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import Table from "@/Components/Table/Table.vue";

defineProps(["payment_accounts", "title", "pageHead"]);

const itemRoute = route().current().replace(/index$/i, "show");

function routeParameters(payment_account) {
    switch (route().current()) {
        case "accounting.payment-service-providers.show.payment-accounts.index":
            return [payment_account["payment_service_providers_slug"], payment_account.slug];

        default:
            return [payment_account.slug];
    }
}

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="payment_accounts" class="mt-5">


        <template #cell(code)="{ item: payment_account }">
            <Link :href="route(itemRoute,routeParameters(payment_account))">
                {{ payment_account["code"] }}
            </Link>
        </template>

    </Table>
</template>


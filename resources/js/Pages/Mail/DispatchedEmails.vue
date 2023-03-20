<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import Table from "@/Components/Table/Table.vue";

defineProps(["dispatched_emails", "title", "pageHead"]);

const itemRoute = route().current().replace(/index$/i, "show");

function routeParameters(dispatchedEmail) {
    switch (route().current()) {
        case "mail.mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.index":
            return [dispatchedEmail["mailrooms_slug"], dispatchedEmail.slug];

        default:
            return [dispatchedEmail.slug];
    }
}

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="dispatched_emails" class="mt-5">


        <template #cell(name)="{ item: dispatchedEmail }">
            <Link :href="route(itemRoute,routeParameters(dispatchedEmail))">
                {{ dispatchedEmail["name"] }}
            </Link>
        </template>

    </Table>
</template>


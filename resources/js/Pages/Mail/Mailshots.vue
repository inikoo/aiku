<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import Table from "@/Components/Table/Table.vue";

defineProps(["mailshots", "title", "pageHead"]);

const itemRoute = route().current().replace(/index$/i, "show");

function routeParameters(mailshot) {
    switch (route().current()) {
        case "mail.mailrooms.show.outboxes.show.mailshots.index":
            return [mailshot["mailrooms_slug"], mailshot.slug];

        default:
            return [mailshot.slug];
    }
}

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="mailshots" class="mt-5">


        <template #cell(name)="{ item: mailshot }">
            <Link :href="route(itemRoute,routeParameters(mailshot))">
                {{ mailshot["name"] }}
            </Link>
        </template>

    </Table>
</template>


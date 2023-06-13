<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Tue, 28 Feb 2023 10:07:36 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';
import { capitalize } from "@/Composables/capitalize"

defineProps(['mailrooms', 'title', 'pageHead']);

</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="mailrooms" class="mt-5">


        <template #cell(code)="{ item: mailroom }">
            <Link :href="route('mail.mailrooms.show',[mailroom['slug']])">
                {{ mailroom['slug'] }}
            </Link>
        </template>
        <template #cell(number_outboxes)="{ item: mailroom }">
            <Link :href="route('mail.mailrooms.show.outboxes.index',[mailroom['slug']])">
                {{ mailroom['number_outboxes'] }}
            </Link>
        </template>
        <template #cell(number_mailshots)="{ item: mailroom }">
            <Link :href="route('mail.mailrooms.show.outboxes.show.mailshots.index',[mailroom['slug']])">
                {{ mailroom['number_payments'] }}
            </Link>
        </template>
        <template #cell(number_dispatched_emails)="{ item: mailroom }">
            <Link :href="route('mail.mailrooms.show.outboxes.show.mailshots.show.dispatched_emails.index',[mailroom['slug']])">
                {{ mailroom['number_dispatched_emails'] }}
            </Link>
        </template>
    </Table>
</template>


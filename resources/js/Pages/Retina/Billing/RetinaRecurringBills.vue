<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'

import { capitalize } from "@/Composables/capitalize"
import { ref } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import Table from "@/Components/Table/Table.vue"
import { RecurringBill } from '@/types/recurring_bill'

// import FileShowcase from '@/xxxxxxxxxxxx'

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    data: {}
    
}>()

function recurringBillRoute(recurringBill: RecurringBill) {
    switch (route().current()) {
        default:
            return route(
                'retina.billing.recurring.show',
                [
                    recurringBill.slug
                ])

    }
}

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(reference)="{ item: recurringBill }">
            <Link :href="recurringBillRoute(recurringBill)" class="primaryLink">
                {{ recurringBill['reference'] }}
            </Link>
        </template>
    </Table>
</template>
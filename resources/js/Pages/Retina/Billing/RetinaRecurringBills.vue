<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'

import { capitalize } from "@/Composables/capitalize"
import { ref } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import Table from "@/Components/Table/Table.vue"
import { RecurringBill } from '@/types/recurring_bill'

import { useLocaleStore } from '@/Stores/locale'
import { useFormatTime } from '@/Composables/useFormatTime'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faReceipt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faReceipt)

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

           <!-- Column: Net -->
       <!--  <template #cell(net_amount)="{ item: bill }">
            <div class="text-gray-500">
                {{ useLocaleStore().currencyFormat(bill.currency_code, bill.net_amount) }}
            </div>
        </template> -->

        <!-- Column: Start date -->
        <template #cell(start_date)="{ item }">
            {{ useFormatTime(item.start_date) }}
        </template>

        <!-- Column: End date -->
        <template #cell(end_date)="{ item }">
            {{ useFormatTime(item.end_date) }}
        </template>
    </Table>
</template>
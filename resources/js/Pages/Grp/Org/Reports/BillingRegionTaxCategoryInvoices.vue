<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faGlobeEurope } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faGlobeEurope)

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: {}
    currency_code: string
}>()

const locale = useLocaleStore()

function invoiceRoute(invoice: { slug: string, type: string }) {
    const organisation = (route().params as { organisation: string }).organisation

    return invoice.type === "invoice"
        ? route("grp.org.accounting.invoices.show", [organisation, invoice.slug])
        : route("grp.org.accounting.refunds.show", [organisation, invoice.slug])
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(reference)="{ item }">
            <Link :href="invoiceRoute(item)" class="whitespace-nowrap hover:underline">{{ item.reference }}</Link>
        </template>
        <template #cell(type)="{ item }">
            {{ item.type === "invoice" ? ctrans("Invoice") : ctrans("Refund") }}
        </template>
        <template #cell(date)="{ item }">
            {{ useFormatTime(item.date) }}
        </template>
        <template #cell(org_net_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_net_amount) }}
        </template>
        <template #cell(org_tax_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_tax_amount) }}
        </template>
        <template #cell(org_total_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_total_amount) }}
        </template>
    </Table>
</template>

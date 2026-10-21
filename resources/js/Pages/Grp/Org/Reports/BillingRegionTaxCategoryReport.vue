<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faGlobeEurope } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faGlobeEurope)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: {}
    currency_code: string
    billing_region_labels: Record<string, string>
}>()

const locale = useLocaleStore()

function invoicesRoute(item: { billing_region: string, tax_category_id: number }) {
    const between = (route().params as { between?: { date?: string } }).between

    return route("grp.org.reports.billing-region-tax-code.invoices", {
        organisation: (route().params as { organisation: string }).organisation,
        billingRegion: item.billing_region,
        taxCategory: item.tax_category_id,
        ...(between?.date ? { between: { date: between.date } } : {}),
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(billing_region)="{ item }">
            {{ billing_region_labels[item.billing_region] ?? item.billing_region }}
        </template>
        <template #cell(tax_code)="{ item }">
            <span class="whitespace-nowrap">{{ item.tax_code }}</span>
            <span class="ml-2 text-gray-400">{{ item.tax_category_name }}</span>
        </template>
        <template #cell(number_invoices)="{ item }">
            <Link :href="invoicesRoute(item)" class="hover:underline">{{ locale.number(item.number_invoices) }}</Link>
        </template>
        <template #cell(number_refunds)="{ item }">
            <Link :href="invoicesRoute(item)" class="hover:underline">{{ locale.number(item.number_refunds) }}</Link>
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
        <template #footerRows-cell(org_net_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_net_amount) }}
        </template>
        <template #footerRows-cell(org_tax_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_tax_amount) }}
        </template>
        <template #footerRows-cell(org_total_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_total_amount) }}
        </template>
    </Table>
</template>

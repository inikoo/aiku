<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { faGlobe, faFileInvoiceDollar, faArrowCircleLeft, faShoppingCart } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faGlobe, faFileInvoiceDollar, faArrowCircleLeft, faShoppingCart)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: {}
    currency_code: string
    countryInvoicesRoute: {
        name: string
        parameters: Record<string, string>
    }
}>()

const locale = useLocaleStore()

function countryInvoicesRoute(countryCode: string) {
    const between = (route().params as { between?: { date?: string } }).between

    return route(props.countryInvoicesRoute.name, {
        ...props.countryInvoicesRoute.parameters,
        country: countryCode,
        ...(between?.date ? { between: { date: between.date } } : {}),
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(country_name)="{ item }">
            <Link v-if="item.country_code" :href="countryInvoicesRoute(item.country_code)" class="font-medium hover:underline">
                {{ item.country_name }}
            </Link>
            <span v-else class="text-gray-400">{{ ctrans("Unknown") }}</span>
        </template>
        <template #cell(org_net_amount)="{ item }">
            {{ locale.currencyFormat(currency_code, item.org_net_amount) }}
        </template>
    </Table>
</template>

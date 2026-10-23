<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Table from '@/Components/Table/Table.vue'
import { capitalize } from '@/Composables/capitalize'
import { ctrans } from '@/Composables/useTrans'
import { PageHeadingTypes } from '@/types/PageHeading'
import { RouteParams } from '@/types/route-params'

defineProps<{
    pageHead: PageHeadingTypes
    title: string
    data: {}
}>()

const compositionRoute = (masterProduct: { slug: string }) =>
    route('grp.masters.master_shops.show.master_products.composition', {
        masterShop: (route().params as RouteParams).masterShop,
        masterProduct: masterProduct.slug,
    })
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(code)="{ item: masterProduct }">
            <Link
                :href="compositionRoute(masterProduct)"
                v-tooltip="ctrans('Choose its barcode')"
                class="secondaryLink whitespace-nowrap"
            >
                {{ masterProduct.code }}
            </Link>
        </template>
        <template #cell(trade_units)="{ item: masterProduct }">
            <span class="whitespace-nowrap">{{ masterProduct.trade_units }}</span>
        </template>
    </Table>
</template>

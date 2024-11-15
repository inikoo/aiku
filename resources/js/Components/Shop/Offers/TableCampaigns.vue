<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Github: aqordeon
    -  Created: Mon, 9 September 2024 16:24:07 Bali, Indonesia
    -  Copyright (c) 2024, Vika Aqordi
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Order } from "@/types/order"
import type { Links, Meta, Table as TableTS } from "@/types/Table"
// import { useFormatTime } from '@/Composables/useFormatTime'
import Icon from "@/Components/Icon.vue"

defineProps<{
    data: TableTS
    tab?: string
}>()


function campaignRoute(campaign: {}) {
    // console.log(route().current())
    switch (route().current()) {
        case "grp.org.shops.show.discounts.campaigns.index":
            return route(
                "grp.org.shops.show.discounts.campaigns.show",
                [route().params["organisation"], route().params["shop"], campaign.slug])
        default:
            return ''
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(state)="{ item: campaign }">
            <Icon :data="campaign.state_icon" />
        </template>
        <template #cell(code)="{ item: campaign }">
            <Link :href="campaignRoute(campaign)" class="primaryLink">
            {{ campaign.code }}
            </Link>
        </template>


        <!-- Column: Date -->
        <!-- <template #cell(date)="{ item: order }">
            {{ useFormatTime(order.date) }}
        </template> -->
    </Table>
</template>

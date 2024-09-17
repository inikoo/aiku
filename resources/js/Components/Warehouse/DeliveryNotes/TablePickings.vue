<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Github: aqordeon
    -  Created: Mon, 13 September 2024 11:24:07 Bali, Indonesia
    -  Copyright (c) 2024, Vika Aqordi
-->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Order } from "@/types/order"
import type { Links, Meta, Table as TableTS } from "@/types/Table"
import { routeType } from "@/types/route"
import PureMultiselectInfiniteScroll from "@/Components/Pure/PureMultiselectInfiniteScroll.vue"
import { trans } from "laravel-vue-i18n"
// import { useFormatTime } from '@/Composables/useFormatTime'

defineProps<{
    data: TableTS
    tab?: string
    routes: {
        pickers_list: routeType
        packers_list: routeType
    }
}>()


function deliveryNoteRoute(deliveryNote: Order) {
    // console.log(route().current())
    switch (route().current()) {
        case "grp.org.warehouses.show.dispatching.delivery-notes.show":
            // return route(
            //     "grp.org.shops.show.discounts.campaigns.show",
            //     [route().params["organisation"], , route().params["shop"], route().params["customer"], deliveryNote.slug])
        default:
            return ''
    }
}


</script>

<template>
    <!-- <pre>{{ data.data[0] }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(org_stock_code)="{ item: deliveryNote }">
            <Link :href="deliveryNoteRoute(deliveryNote)" class="primaryLink">
                {{ deliveryNote.org_stock_code }}
            </Link>
        </template>

        <!-- Column: Picker Name -->
        <template #cell(picker_name)="{ item }">
            <div class="relative w-[200px]">
                <PureMultiselectInfiniteScroll
                    v-model="item.id"
                    :fetchRoute="routes.pickers_list"
                    :placeholder="trans('Select picker')"
                    valueProp="alias"
                    labelProp="contact_name"
                    @optionsList="(options) => false"
                >
                    <template #singlelabel="{ value }">
                        <div class="w-full text-left pl-3 pr-2 text-sm whitespace-nowrap truncate">
                            {{ value.contact_name }}
                        </div>
                    </template>

                    <template #option="{ option, isSelected, isPointed }">
                        <div class="w-full text-left text-sm whitespace-nowrap truncate">
                            {{ option.contact_name }}
                        </div>
                    </template>

                </PureMultiselectInfiniteScroll>
            </div>
        </template>

        <!-- Column: Packer Name -->
        <template #cell(packer_name)="{ item }">
            <div class="relative w-[200px]">
                <PureMultiselectInfiniteScroll
                    v-model="item.id"
                    :fetchRoute="routes.packers_list"
                    :placeholder="trans('Select picker')"
                    valueProp="alias"
                    labelProp="contact_name"
                    @optionsList="(options) => false"
                >
                    <template #singlelabel="{ value }">
                        <div class="w-full text-left pl-3 pr-2 text-sm whitespace-nowrap truncate">
                            {{ value.contact_name }}
                        </div>
                    </template>

                    <template #option="{ option, isSelected, isPointed }">
                        <div class="w-full text-left text-sm whitespace-nowrap truncate">
                            {{ option.contact_name }}
                        </div>
                    </template>

                </PureMultiselectInfiniteScroll>
            </div>
        </template>


        <!-- Column: Date -->
        <!-- <template #cell(date)="{ item: order }">
            {{ useFormatTime(order.date) }}
        </template> -->
    </Table>
</template>

<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Github: aqordeon
    -  Created: Mon, 13 September 2024 11:24:07 Bali, Indonesia
    -  Copyright (c) 2024, Vika Aqordi
-->

<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Order } from "@/types/order"
import type { Links, Meta, Table as TableTS } from "@/types/Table"
import { routeType } from "@/types/route"
import PureMultiselectInfiniteScroll from "@/Components/Pure/PureMultiselectInfiniteScroll.vue"
import { trans } from "laravel-vue-i18n"
import { ref } from "vue"

// import { useFormatTime } from '@/Composables/useFormatTime'
import { useTruncate } from '@/Composables/useTruncate'
import Action from "@/Components/Forms/Fields/Action.vue"

defineProps<{
    data: TableTS
    tab?: string
    routes: {
        pickers_list: routeType
        packers_list: routeType
    }
    state: string
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


const isLoading = ref<{[key: string]: boolean}>({})
const onSubmitPickerPacker = (fetchRoute: routeType, selectedPicker: {}, rowIndex: number, scope: string) => {
    console.log('dd', selectedPicker)
    try {
        router.patch(route(fetchRoute.name, fetchRoute.parameters), {
            [`${scope}_id`]: selectedPicker.user_id
        }, {
            onStart: () => isLoading.value[rowIndex + scope + selectedPicker.user_id] = true,
            onFinish: () => isLoading.value[rowIndex + scope + selectedPicker.user_id] = false,
            preserveScroll: true
        })
    } catch (error) {
        
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
        <!-- Column: Date -->
        <template #cell(actions)="{ item }">
            <!-- <pre>{{item}}</pre> -->
            <!-- {{item.routes.doneRoute}} -->
            <Action v-if="state === 'picking' && !item.vessel_picking" :action="{ label: 'Pick', type: 'secondary', route: item.routes.pickedRoute, key: 'picking_item' + item.index}" />
            <Action v-if="state === 'packing' && !item.vessel_packing" :action="{ label: 'Pack', type: 'secondary', route: item.routes.doneRoute, key: 'packing_item' + item.index}" />
        </template>
    </Table>
</template>

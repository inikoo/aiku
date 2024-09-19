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

        <!-- Column: Picker Name -->
        <template #cell(picker_name)="{ item }">
            <div v-if="state === 'submitted' || state === 'in_queue' " class="relative w-[170px]">
                <PureMultiselectInfiniteScroll
                    v-model="item.picker.selected"
                    @update:modelValue="(selectedPicker) => onSubmitPickerPacker(item.assign_picker, selectedPicker, item.rowIndex, 'picker')"
                    :fetchRoute="routes.pickers_list"
                    :placeholder="trans('Select picker')"
                    labelProp="contact_name"
                    valueProp="user_id"
                    object
                    :loading="isLoading[item.rowIndex + 'picker' + item.picker?.selected?.user_id]"
                    :disabled="isLoading[item.rowIndex + 'picker' + item.picker?.selected?.user_id]"
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
            <div v-else class="whitespace-nowrap" v-tooltip="item.picker?.selected?.contact_name?.length > 15 ? item.picker?.selected?.contact_name : undefined">
                <span v-if="item.picker?.selected?.contact_name">{{ useTruncate(item.picker?.selected?.contact_name, 15) }}</span>
                <span v-else class="text-gray-400 italic text-sm">{{ trans('Not set yet') }}</span>
            </div>
        </template>

        <!-- Column: Packer Name -->
        <template #cell(packer_name)="{ item }">
            <div v-if="state === 'packing'" class="relative w-[170px]">
                <PureMultiselectInfiniteScroll
                    v-model="item.packer.selected"
                    @update:modelValue="(selectedPacker) => onSubmitPickerPacker(item.assign_packer, selectedPacker, item.rowIndex, 'packer')"
                    :fetchRoute="routes.packers_list"
                    :placeholder="trans('Select packer')"
                    labelProp="contact_name"
                    valueProp="user_id"
                    object
                    :loading="isLoading[item.rowIndex + 'packer' + item.packer?.selected?.user_id]"
                    :disabled="isLoading[item.rowIndex + 'packer' + item.packer?.selected?.user_id]"
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
            <div v-else class="whitespace-nowrap" v-tooltip="item.packer?.selected?.contact_name?.length > 15 ? item.packer?.selected?.contact_name : undefined">
                <span v-if="item.packer?.selected?.contact_name">{{ useTruncate(item.packer?.selected?.contact_name, 15) }}</span>
                <span v-else class="text-gray-400 italic text-sm">{{ trans('Not set yet') }}</span>
            </div>
        </template>


        <!-- Column: Date -->
        <template #cell(actions)="{ item }">
            <!-- {{ useFormatTime(order.date) }} -->
            <Action v-if="state === 'picking'" :action="{ label: 'Pick', route: item.routes.pickedRoute, key: 'picking_item1'}" />
        </template>
    </Table>
</template>

<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Created on: 26-08-2024, Bali, Indonesia
    -  Github: https://github.com/aqordeon
    -  Copyright: 2024
-->

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from 'vue'
import type { Component } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TablePalletDeliveryPallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePalletDeliveryPallets.vue'
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import { get } from 'lodash'
import { faExclamationTriangle } from '@fad'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import { PalletDelivery, BoxStats, PDRNotes, UploadPallet } from '@/types/Pallet'
import { Table as TableTS } from '@/types/Table'
import { Tabs as TSTabs } from '@/types/Tabs'
import '@vuepic/vue-datepicker/dist/main.css'
import BoxStatsPalletDelivery from '@/Pages/Grp/Org/Fulfilment/Delivery/BoxStatsPalletDelivery.vue'

import '@/Composables/Icon/PalletDeliveryStateEnum'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone, faConciergeBell, faCube, faCalendarDay, faPencil } from '@fal'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue";

import axios from 'axios'
import { Action } from '@/types/Action'
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
import { notify } from '@kyvg/vue3-notification'
import OrderProductTable from '@/Components/Dropshipping/Orders/OrderProductTable.vue'
import { Button as TSButton } from '@/types/Button'
import PureMultiselectInfiniteScroll from '@/Components/Pure/PureMultiselectInfiniteScroll.vue'

library.add(faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone,faExclamationTriangle, faConciergeBell, faCube, faCalendarDay, faPencil)



const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: TableTS
    services?: TableTS
    physical_goods?: TableTS
    data?: {
        data: PalletDelivery
    }
    pageHead: PageHeadingTypes
    updateRoute: routeType

    interest: {
        pallets_storage: boolean
        items_storage: boolean
        dropshipping: boolean
    }

    // uploadRoutes: {
    //     upload: routeType
    //     download: routeType
    //     history: routeType
    // }

    upload_spreadsheet: UploadPallet

    locationRoute: routeType
    rentalRoute: routeType
    storedItemsRoute: {
        index: routeType
        store: routeType
    }
    box_stats: BoxStats
    pallet_limits?: {
        status: string
        message: string
    }

    routes: {
        products_list: routeType
    }
}>()


const currentTab = ref(props.tabs?.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        products: OrderProductTable
    }

    return components[currentTab.value]
})


const isLoadingButton = ref<string | boolean>(false)
const isLoadingData = ref<string | boolean>(false)

// Tabs: Products
const formProducts = useForm({ selectedId : [], quantity: 1,  })
const onSubmitAddProducts = (data: Action, closedPopover: Function) => {
    isLoadingButton.value = 'addProducts'

    formProducts.post(
        route(data.route?.name || '#', {...data.route?.parameters }),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formProducts.reset()
            },
            onError: (errors) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to add service, please try again.',
                    type: 'error',
                })
            },
            onFinish: () => {
                isLoadingButton.value = false
            }
        }
    )
}

</script>

<template>
    <!-- <pre>{{ data.data }}</pre> -->
    <!-- {{ props.service_list_route.name }} -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Add service -->
        <template #button-add-products="{ action }">
            <div class="relative">
                <Popover>
                    <template #button="{open}">
                        <Button
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                        />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <div class="text-xs px-1 my-2">{{ trans('Products') }}: </div>
                            <div class="">
                                <PureMultiselectInfiniteScroll
                                    v-model="formProducts.selectedId"
                                    :fetchRoute="routes.products_list"
                                    :placeholder="trans('Select Products')"
                                    valueProp="id"
                                />

                                <p v-if="get(formProducts, ['errors', 'selectedId'])" class="mt-2 text-sm text-red-500">
                                    {{ formProducts.errors.selectedId }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <div class="text-xs px-1 my-2">{{ trans('Quantity') }}: </div>
                                <PureInput
                                    v-model="formProducts.quantity"
                                    :placeholder="trans('Quantity')"
                                    @keydown.enter="() => onSubmitAddProducts(action, closed)"
                                />
                                <p v-if="get(formProducts, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
                                    {{ formProducts.errors.quantity }}
                                </p>
                            </div>

                            <div class="flex justify-end mt-4">
                                <Button
                                    @click="() => onSubmitAddProducts(action, closed)"
                                    :style="'save'"
                                    :loading="isLoadingButton == 'addService'"
                                    :disabled="!formProducts.selectedId?.length || (formProducts.quantity < 1)"
                                    label="Save"
                                    full
                                />
                            </div>
                            
                            <!-- Loading: fetching service list -->
                            <div v-if="isLoadingData === 'addService'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>
    </PageHeading>

    <!-- Section: Timeline -->
    <div v-if="props.data?.data?.state != 'in-process'" class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline v-if="props.data?.data?.timeline" :options="props.data?.data?.timeline" :state="props.data?.data?.state" :slidesPerView="6" />
    </div>

    <!-- Box -->
    <BoxStatsPalletDelivery :dataPalletDelivery="data?.data" :boxStats="box_stats" :updateRoute />

    <Tabs :current="currentTab" :navigation="tabs?.navigation" @update:tab="handleTabUpdate" />

    <div class="pb-12">
        <component
            :is="component"
            :data="props[currentTab as keyof typeof props]"
            :tab="currentTab"
        />
    </div>

</template>

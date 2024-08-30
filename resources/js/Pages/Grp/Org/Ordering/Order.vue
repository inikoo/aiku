<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Created on: 26-08-2024, Bali, Indonesia
    -  Github: https://github.com/aqordeon
    -  Copyright: 2024
-->

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
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


import PureMultiselect from "@/Components/Pure/PureMultiselect.vue";
import { Timeline as TSTimeline } from "@/types/Timeline"

import axios from 'axios'
import { Action } from '@/types/Action'
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
import { notify } from '@kyvg/vue3-notification'
import OrderProductTable from '@/Components/Dropshipping/Orders/OrderProductTable.vue'
import { Button as TSButton } from '@/types/Button'
import PureMultiselectInfiniteScroll from '@/Components/Pure/PureMultiselectInfiniteScroll.vue'
import NeedToPay from '@/Components/Utils/NeedToPay.vue'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'

import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import Modal from '@/Components/Utils/Modal.vue'
import ModalAddress from '@/Components/Utils/ModalAddress.vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle, faExclamation } from '@fas'
import {  faDollarSign, faIdCardAlt, faShippingFast, faIdCard, faEnvelope, faPhone } from '@fal'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faExclamationTriangle, faDollarSign, faIdCardAlt, faShippingFast, faIdCard, faEnvelope, faPhone, faExclamation)


const props = defineProps<{
    title: string
    tabs: TSTabs
    
    products: TableTS

    data?: {
        data: PalletDelivery
    }
    timeline: TSTimeline

    pageHead: PageHeadingTypes
    // updateRoute: routeType

    // interest: {
    //     pallets_storage: boolean
    //     items_storage: boolean
    //     dropshipping: boolean
    // }

    // uploadRoutes: {
    //     upload: routeType
    //     download: routeType
    //     history: routeType
    // }

    upload_spreadsheet: UploadPallet

    // locationRoute: routeType
    // rentalRoute: routeType
    // storedItemsRoute: {
    //     index: routeType
    //     store: routeType
    // }
    box_stats: {
        customer: {

        }
        products: {

        }
        order_sumaary: {
            
        }
    }
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
// const isLoadingData = ref<string | boolean>(false)
const isModalAddress = ref<boolean>(false)

// Tabs: Products
const formProducts = useForm({ historicAssetId : null, quantity_ordered: 1,  })
const onSubmitAddProducts = (data: Action, closedPopover: Function) => {
    isLoadingButton.value = 'addProducts'

    formProducts
        .transform((data) => ({
            quantity_ordered: data.quantity_ordered,
        }))
        .post(
            route(data.route?.name || '#', {...data.route?.parameters, historicAsset: formProducts.historicAssetId }),
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
                                    v-model="formProducts.historicAssetId"
                                    :fetchRoute="routes.products_list"
                                    :placeholder="trans('Select Products')"
                                    valueProp="current_historic_asset_id"
                                >
                                    <template #singlelabel="{ value }">
                                        <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ value.stock }})</span></div>
                                    </template>

                                    <template #option="{ option, isSelected, isPointed }">
                                        <div class="w-full flex items-center justify-between gap-x-3">
                                            <div :class="isSelected(option) ? option.stock ? '' : 'text-indigo-200' : option.stock ? '' : 'text-gray-400'">{{ option.name }} <span class="text-sm" :class="isSelected(option) ? 'text-indigo-200' : 'text-gray-400'">({{ option.stock }})</span></div>
                                            
                                            <FontAwesomeIcon v-if="option.stock === 0" v-tooltip="trans('No stock')" icon='fas fa-exclamation-triangle' class='text-red-500' fixed-width aria-hidden='true' />
                                            <FontAwesomeIcon v-else-if="option.stock < 10" icon='fas fa-exclamation' class='text-yellow-500' fixed-width aria-hidden='true' />
                                        </div>
                                    </template>
                                </PureMultiselectInfiniteScroll>

                                <p v-if="get(formProducts, ['errors', 'historicAssetId'])" class="mt-2 text-sm text-red-500">
                                    {{ formProducts.errors.historicAssetId }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <div class="text-xs px-1 my-2">{{ trans('Quantity') }}: </div>
                                <PureInput
                                    v-model="formProducts.quantity_ordered"
                                    :placeholder="trans('Quantity')"
                                    @keydown.enter="() => onSubmitAddProducts(action, closed)"
                                />
                                <p v-if="get(formProducts, ['errors', 'quantity_ordered'])" class="mt-2 text-sm text-red-600">
                                    {{ formProducts.errors.quantity_ordered }}
                                </p>
                            </div>

                            <div class="flex justify-end mt-4">
                                <Button
                                    @click="() => onSubmitAddProducts(action, closed)"
                                    :style="'save'"
                                    :loading="isLoadingButton == 'addProducts'"
                                    :disabled="!formProducts.historicAssetId || (formProducts.quantity_ordered < 1)"
                                    label="Save"
                                    full
                                />
                            </div>
                            
                            <!-- Loading: fetching service list -->
                            <!-- <div v-if="isLoadingData === 'addProducts'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div> -->
                        </div>
                    </template>
                </Popover>
            </div>
        </template>
    </PageHeading>

    <!-- Section: Timeline -->
    <div v-if="props.data?.data?.state != 'in-process'" class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline v-if="timeline" :options="timeline" :state="props.data?.data?.state" :slidesPerView="6" />
    </div>

    <!-- Box -->
    <!-- <BoxStatsPalletDelivery :dataPalletDelivery="data?.data" :boxStats="box_stats" :updateRoute /> -->
    
    <div class="grid grid-cols-4 divide-x divide-gray-300 border-b border-gray-200">
        
        <BoxStatPallet class=" py-2 px-3" icon="fal fa-user">
            <!-- Field: Registration Number -->
            <Link as="a" v-if="box_stats?.customer.reference" :href="'route(box_stats?.customer.route.name, box_stats?.customer.route.parameters)'"
                class="pl-1 flex items-center w-fit flex-none gap-x-2 cursor-pointer primaryLink">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500" v-tooltip="'Reference'">#{{ box_stats?.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="box_stats?.customer.contact_name" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500" v-tooltip="'Contact name'">{{ box_stats?.customer.contact_name }}</dd>
            </div>

            <!-- Field: Company name -->
            <div v-if="box_stats?.customer.company_name" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500" v-tooltip="'Company name'">{{ box_stats?.customer.company_name }}</dd>
            </div>

            
            <!-- Field: Email -->
            <div v-if="box_stats?.customer.email" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-envelope' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <a :href="`mailto:${box_stats?.customer.email}`" v-tooltip="'Click to send email'" class="text-xs text-gray-500 hover:text-gray-700">{{ box_stats?.customer.email }}</a>
            </div>
            
            <!-- Field: Phone -->
            <div v-if="box_stats?.customer.phone" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-phone' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <a :href="`tel:${box_stats?.customer.phone}`" v-tooltip="'Click to make a phone call'" class="text-xs text-gray-500 hover:text-gray-700">{{ box_stats?.customer.phone }}</a>
            </div>

            <!-- Field: Address -->
            <div v-if="box_stats?.customer.address" class="pl-1 flex items w-full flex-none gap-x-2" v-tooltip="trans('Shipping address')">
                <dt v-tooltip="'Address'" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-shipping-fast' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="w-full text-gray-500 text-xs">
                    <div class="relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50">
                        <span class="" v-html="box_stats?.customer.address.formatted_address" />

                        <div @click="() => isModalAddress = true"
                            class="whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                            <!-- <FontAwesomeIcon icon='fal fa-pencil' size="sm" class='mr-1' fixed-width aria-hidden='true' /> -->
                            <span>{{ trans('Edit') }}</span>
                        </div>
                    </div>
                </dd>
            </div>
        </BoxStatPallet>

        <!-- Box: Product stats -->
        <BoxStatPallet class="py-4 pl-1 pr-2" icon="fal fa-user">
            <div class="relative flex items-start w-full flex-none gap-x-2">
                <dt class="flex-none pt-1">
                    <FontAwesomeIcon icon='fal fa-dollar-sign' fixed-width aria-hidden='true' class="text-gray-500" />
                </dt>

                <NeedToPay 
                    :payAmount="0"
                    :paidAmount="99"
                    :totalAmount="11"
                />
            </div>
        </BoxStatPallet>

        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
                <!-- <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2> -->

                <OrderSummary :order_summary="box_stats.order_summary" />

                <!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </BoxStatPallet>
    </div>

    <Tabs :current="currentTab" :navigation="tabs?.navigation" @update:tab="handleTabUpdate" />

<!-- <pre>{{ timeline }}</pre> -->
    <div class="pb-12">
        <component
            :is="component"
            :data="props[currentTab as keyof typeof props]"
            :tab="currentTab"
        />
    </div>

    <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
        <ModalAddress
            :addresses="data.addresses"
            :updateRoute="data.address_update_route"
        />
    </Modal>
</template>

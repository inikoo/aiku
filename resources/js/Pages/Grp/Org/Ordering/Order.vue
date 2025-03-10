<!--
    -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
    -  Created on: 26-08-2024, Bali, Indonesia
    -  Github: https://github.com/aqordeon
    -  Copyright: 2024
-->

<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
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
import { get } from 'lodash-es'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'
import { PalletDelivery, BoxStats, PDRNotes, UploadPallet } from '@/types/Pallet'
import { Table as TableTS } from '@/types/Table'
import { Tabs as TSTabs } from '@/types/Tabs'
import '@vuepic/vue-datepicker/dist/main.css'

import '@/Composables/Icon/PalletDeliveryStateEnum'


import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import PureTextarea from '@/Components/Pure/PureTextarea.vue'
import { Timeline as TSTimeline } from "@/types/Timeline"

import axios from 'axios'
import { Action } from '@/types/Action'
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue"
import TableDeliveryNotes from "@/Components/Tables/Grp/Org/Dispatching/TableDeliveryNotes.vue"
import { notify } from '@kyvg/vue3-notification'
import OrderProductTable from '@/Components/Dropshipping/Orders/OrderProductTable.vue'
import { Button as TSButton } from '@/types/Button'
import PureMultiselectInfiniteScroll from '@/Components/Pure/PureMultiselectInfiniteScroll.vue'
import NeedToPay from '@/Components/Utils/NeedToPay.vue'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'

import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import Modal from '@/Components/Utils/Modal.vue'
import ModalAddress from '@/Components/Utils/ModalAddress.vue'
import { Address, AddressManagement } from "@/types/PureComponent/Address"

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import AlertMessage from '@/Components/Utils/AlertMessage.vue'
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue"
import UploadAttachment from '@/Components/Upload/UploadAttachment.vue'

import { faExclamationTriangle as fadExclamationTriangle } from '@fad'
import { faExclamationTriangle, faExclamation } from '@fas'
import { faDollarSign, faIdCardAlt, faShippingFast, faIdCard, faEnvelope, faPhone, faWeight, faStickyNote, faTruck, faFilePdf, faPaperclip, } from '@fal'
import { Currency } from '@/types/LayoutRules'
import TableInvoices from '@/Components/Tables/Grp/Org/Accounting/TableInvoices.vue'
import ModalProductList from '@/Components/Utils/ModalProductList.vue'
import TableProductList from '@/Components/Tables/Grp/Helpers/TableProductList.vue'
import { faSpinnerThird } from '@far'
library.add(fadExclamationTriangle, faExclamationTriangle, faDollarSign, faIdCardAlt, faShippingFast, faIdCard, faEnvelope, faPhone, faWeight, faStickyNote, faExclamation, faTruck, faFilePdf, faPaperclip, faSpinnerThird)


const props = defineProps<{
    title: string
    tabs: TSTabs

    products: TableTS

    data?: {
        data: PalletDelivery
    }

    pageHead: PageHeadingTypes
    alert?: {
        status: string
        title?: string
        description?: string
    }
    notes: {
        note_list: {
            label: string
            note: string
            editable?: boolean
            bgColor?: string
            textColor?: string
            color?: string
            lockMessage?: string
            field: string  // customer_notes, public_notes, internal_notes
        }[]
        // updateRoute: routeType
    }
    timelines: {
        [key: string]: TSTimeline
    }

    upload_spreadsheet: UploadPallet

    box_stats: {
        customer: {
            reference: string
            contact_name: string
            company_name: string
            email: string
            phone: string
            addresses: {
                delivery: Address
                billing: Address
            }
        }
        products: {
            payment: {
                routes: {
                    fetch_payment_accounts: routeType
                    submit_payment: routeType
                }
                total_amount: number
                paid_amount: number
                pay_amount: number
            }
            estimated_weight: number
        }
        order_summary: {

        }
    }
    pallet_limits?: {
        status: string
        message: string
    }

    routes: {
        updateOrderRoute: routeType
        products_list: routeType
        delivery_note: routeType
    }
    // nonProductItems: {}
    transactions: {}
    currency: Currency
    delivery_notes: {
        data: Array<any>
    },
    delivery_note: {
        reference: String
    }
    attachments?: {}
    invoices?: {}
    attachmentRoutes?: {}
    address_update_route: routeType
    addresses: {
        
    }
}>()


const isModalUploadOpen = ref(false)
const isModaProductListOpen = ref(false)
const locale = inject('locale', aikuLocaleStructure)

const currentTab = ref(props.tabs?.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        transactions: OrderProductTable,
        delivery_notes: TableDeliveryNotes,
        attachments: TableAttachments,
        invoices: TableInvoices,
		products: TableProductList
    }

    return components[currentTab.value]
})


const isLoadingButton = ref<string | boolean>(false)
// const isLoadingData = ref<string | boolean>(false)
const isModalAddress = ref<boolean>(false)

// Tabs: Products
const formProducts = useForm({ historicAssetId: null, quantity_ordered: 1, })
const onSubmitAddProducts = (data: Action, closedPopover: Function) => {
    isLoadingButton.value = 'addProducts'

    formProducts
        .transform((data) => ({
            quantity_ordered: data.quantity_ordered,
        }))
        .post(
            route(data.route?.name || '#', { ...data.route?.parameters, historicAsset: formProducts.historicAssetId }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closedPopover()
                    formProducts.reset()
                },
                onError: (errors) => {
                    notify({
                        title: trans('Something went wrong.'),
                        text: trans('Failed to add service, please try again.'),
                        type: 'error',
                    })
                },
                onFinish: () => {
                    isLoadingButton.value = false
                }
            }
        )
}


// Section: Payment invoice
const listPaymentMethod = ref([])
const isLoadingFetch = ref(false)
const fetchPaymentMethod = async () => {
    try {
        isLoadingFetch.value = true
        const { data } = await axios.get(route(props.box_stats.products.payment.routes.fetch_payment_accounts.name, props.box_stats.products.payment.routes.fetch_payment_accounts.parameters))
        listPaymentMethod.value = data.data
    } catch (error) {
        notify({
            title: trans('Something went wrong'),
            text: trans('Failed to fetch payment method list'),
            type: 'error',
        })
    }
    finally {
        isLoadingFetch.value = false
    }
}

const paymentData = ref({
    payment_method: null as number | null,
    payment_amount: 0 as number | null,
    payment_reference: ''
})
const currentAction = ref(null);
const isOpenModalPayment = ref(false)
const isLoadingPayment = ref(false)
const errorPaymentMethod = ref<null | unknown>(null)
const onSubmitPayment = () => {
    try {
        router[props.box_stats.products.payment.routes.submit_payment.method || 'post'](
            route(props.box_stats.products.payment.routes.submit_payment.name, {
                ...props.box_stats.products.payment.routes.submit_payment.parameters,
                paymentAccount: paymentData.value.payment_method
            }),
            {
                amount: paymentData.value.payment_amount,
                reference: paymentData.value.payment_reference,
                status: 'success',
                state: 'completed',
            },
            {
                onStart: () => isLoadingPayment.value = true,
                onFinish: (response) => {
                    isLoadingPayment.value = false,
                        isOpenModalPayment.value = false,
                        notify({
                            title: trans('Success'),
                            text: trans('Successfully add payment invoice'),
                            type: 'success',
                        })
                },
                onSuccess: (response) => {
                    paymentData.value.payment_method = null, 
                        paymentData.value.payment_amount = 0,
                        paymentData.value.payment_reference = ''
                }
            }
        )

    } catch (error: unknown) {
        errorPaymentMethod.value = error
    }
}


// Section: add notes (on popup pageheading)
const errorNote = ref('')
const noteToSubmit = ref({
    selectedNote: '',
    value: ''
})
const onSubmitNote = async (closePopup: Function) => {

    try {
        router.patch(route(props.routes.updateOrderRoute.name, props.routes.updateOrderRoute.parameters), {
            [noteToSubmit.value.selectedNote]: noteToSubmit.value.value
        },
            {
                headers: { "Content-Type": 'application/json' },
                onStart: () => isLoadingButton.value = 'submitNote',
                onError: (error) => errorNote.value = error,
                onFinish: () => isLoadingButton.value = false,
                onSuccess: () => {
                    closePopup(),
                        noteToSubmit.value.selectedNote = ''
                    noteToSubmit.value.value = ''
                },
            })
    } catch (error) {
        notify({
            title: trans("Something went wrong"),
            text: trans("Failed to update the note, try again."),
            type: "error",
        })
    }
}

const openModal = (action :any) => {
	currentAction.value = action;
    isModaProductListOpen.value = true;
};

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
                    <template #button="{ open }">
						<Button
							:style="action.style"
							:label="action.label"
							:icon="action.icon"
							@click="() => openModal(action)"
							:key="`ActionButton${action.label}${action.style}`"
							:tooltip="action.tooltip" />
					</template>

                 <!--    <template #content="{ close: closed }">
                        <div class="w-[350px]"> -->
                           <!--  <div class="text-xs px-1 my-2">{{ trans('Products') }}: </div>
                            <div class="">
                                <PureMultiselectInfiniteScroll v-model="formProducts.historicAssetId"
                                    :fetchRoute="routes.products_list" :placeholder="trans('Select Products')"
                                    valueProp="current_historic_asset_id">
                                    <template #singlelabel="{ value }">
                                        <div class="w-full text-left pl-4">{{ value.name }} <span
                                                class="text-sm text-gray-400">({{ value.stock }})</span></div>
                                    </template>

                                    <template #option="{ option, isSelected, isPointed }">
                                        <div class="w-full flex items-center justify-between gap-x-3">
                                            <div
                                                :class="isSelected(option) ? option.stock ? '' : 'text-indigo-200' : option.stock ? '' : 'text-gray-400'">
                                                {{ option.name }} <span class="text-sm"
                                                    :class="isSelected(option) ? 'text-indigo-200' : 'text-gray-400'">({{
                                                        option.stock }})</span></div>

                                            <FontAwesomeIcon v-if="option.stock === 0" v-tooltip="trans('No stock')"
                                                icon='fas fa-exclamation-triangle' class='text-red-500' fixed-width
                                                aria-hidden='true' />
                                            <FontAwesomeIcon v-else-if="option.stock < 10" icon='fas fa-exclamation'
                                                class='text-yellow-500' fixed-width aria-hidden='true' />
                                        </div>
                                    </template>
                                </PureMultiselectInfiniteScroll>

                                <p v-if="get(formProducts, ['errors', 'historicAssetId'])"
                                    class="mt-2 text-sm text-red-500">
                                    {{ formProducts.errors.historicAssetId }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <div class="text-xs px-1 my-2">{{ trans('Quantity') }}: </div>
                                <PureInput v-model="formProducts.quantity_ordered" :placeholder="trans('Quantity')"
                                    @keydown.enter="() => onSubmitAddProducts(action, closed)" />
                                <p v-if="get(formProducts, ['errors', 'quantity_ordered'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formProducts.errors.quantity_ordered }}
                                </p>
                            </div>

                            <div class="flex justify-end mt-4">
                                <Button @click="() => onSubmitAddProducts(action, closed)" :style="'save'"
                                    :loading="isLoadingButton == 'addProducts'"
                                    :disabled="!formProducts.historicAssetId || (formProducts.quantity_ordered < 1)"
                                    label="Save" full />
                            </div> -->

                            <!-- Loading: fetching service list -->
                            <!-- <div v-if="isLoadingData === 'addProducts'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div> -->
                 <!--        </div>
                    </template> -->
                </Popover>
            </div>
        </template>

        <template #otherBefore>
            <!-- Section: Add notes -->
            <Popover v-if="!notes?.note_list?.some(item => !!(item?.note?.trim()))">
                <template #button="{ open }">
                    <Button icon="fal fa-sticky-note" type="tertiary" label="Add notes" />
                </template>
                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs px-1 my-2">{{ trans('Select type note') }}: </span>
                        <div class="">
                            <PureMultiselect v-model="noteToSubmit.selectedNote"
                                @update:modelValue="() => errorNote = ''" :placeholder="trans('Select type note')"
                                required
                                :options="[{ label: 'Public note', value: 'public_notes' }, { label: 'Private note', value: 'internal_notes' }]"
                                valueProp="value" />

                            <!-- <p v-if="get(formAddService, ['errors', 'service_id'])" class="mt-2 text-sm text-red-500">
                                {{ formAddService.errors.service_id }}
                            </p> -->
                        </div>

                        <div class="mt-3">
                            <span class="text-xs px-1 my-2">{{ trans('Note') }}: </span>
                            <PureTextarea v-model="noteToSubmit.value" :placeholder="trans('Note')"
                                @keydown.enter="() => onSubmitNote(closed)" />
                        </div>

                        <p v-if="errorNote" class="mt-2 text-sm text-red-600">
                            *{{ errorNote }}
                        </p>

                        <div class="flex justify-end mt-3">
                            <Button @click="() => onSubmitNote(closed)" :style="'save'"
                                :loading="isLoadingButton === 'submitNote'" :disabled="!noteToSubmit.value" label="Save"
                                full />
                        </div>

                        <!-- Loading: fetching service list -->
                        <div v-if="isLoadingButton === 'submitNote'"
                            class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                            <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width
                                aria-hidden='true' />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>
        <template #other>
            <Button v-if="currentTab === 'attachments'" @click="() => isModalUploadOpen = true" label="Attach"
                icon="upload" />
        </template>
    </PageHeading>

    <!-- Section: Pallet Warning -->
    <div v-if="alert?.status" class="p-2 pb-0">
        <AlertMessage :alert />
    </div>

    <!-- Section: Box Note -->
    <div class="relative">
        <Transition name="headlessui">
            <div v-if="notes?.note_list?.some(item => !!(item?.note?.trim()))"
                class="p-2 grid sm:grid-cols-3 gap-y-2 gap-x-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
                <BoxNote v-for="(note, index) in notes.note_list" :key="index + note.label" :noteData="note"
                    :updateRoute="routes.updateOrderRoute" />
            </div>
        </Transition>
    </div>

    <!-- Section: Timeline -->
    <div v-if="props.data?.data?.state != 'in_process' && currentTab != 'products'" class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline v-if="timelines" :options="timelines" :state="props.data?.data?.state" :slidesPerView="6" />
    </div>

    <div v-if="currentTab != 'products'" class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-300 border-b border-gray-200">
        <BoxStatPallet class=" py-2 px-3" icon="fal fa-user">
            <!-- Field: Reference Number -->
            <Link as="a" v-if="box_stats?.customer.reference" v-tooltip="trans('Reference')"
                :href="'route(box_stats?.customer.route.name, box_stats?.customer.route.parameters)'"
                class="pl-1 flex items-center w-fit flex-none gap-x-2 cursor-pointer primaryLink">
            <dt class="flex-none">
                <FontAwesomeIcon icon='fal fa-user' class='text-gray-400' fixed-width aria-hidden='true' />
            </dt>
            <dd class="text-sm text-gray-500">#{{ box_stats?.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="box_stats?.customer.contact_name" v-tooltip="trans('Contact name')"
                class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-id-card-alt' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ box_stats?.customer.contact_name }}</dd>
            </div>

            <!-- Field: Company name -->
            <div v-if="box_stats?.customer.company_name" v-tooltip="trans('Company name')"
                class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-building' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-sm text-gray-500">{{ box_stats?.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="box_stats?.customer.email" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="trans('Email')" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-envelope' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <a :href="`mailto:${box_stats?.customer.email}`" v-tooltip="'Click to send email'"
                    class="text-sm text-gray-500 hover:text-gray-700 truncate">{{ box_stats?.customer.email }}</a>
            </div>

            <!-- Field: Phone -->
            <div v-if="box_stats?.customer.phone" class="pl-1 flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="trans('Phone')" class="flex-none">
                    <FontAwesomeIcon icon='fal fa-phone' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <a :href="`tel:${box_stats?.customer.phone}`" v-tooltip="'Click to make a phone call'"
                    class="text-sm text-gray-500 hover:text-gray-700">{{ box_stats?.customer.phone }}</a>
            </div>

            <!-- Field: Billing Address -->
            <div v-if="box_stats?.customer?.addresses?.billing?.formatted_address !== box_stats?.customer?.addresses?.delivery?.formatted_address"
                class="pl-1 flex items w-full flex-none gap-x-2" v-tooltip="trans('Billing address')">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-dollar-sign' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="w-full text-gray-500 text-xs relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50"
                    v-html="box_stats?.customer.addresses.billing.formatted_address">
                </dd>
            </div>

            <!-- Field: Shipping Address -->
            <div v-if="box_stats?.customer?.addresses?.delivery?.formatted_address !== box_stats?.customer?.addresses?.billing?.formatted_address"
                class="mt-2 pl-1 flex items w-full flex-none gap-x-2" v-tooltip="trans('Shipping address')">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-shipping-fast' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="w-full text-gray-500 text-xs relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50"
                    v-html="box_stats?.customer.addresses.delivery.formatted_address">
                </dd>
            </div>

            <div v-if="box_stats?.customer?.addresses?.delivery?.formatted_address === box_stats?.customer?.addresses?.billing?.formatted_address"
                class="mt-2 pl-1 flex items w-full flex-none gap-x-2" v-tooltip="trans('Shipping address and Billing address')">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-shipping-fast' class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="w-full text-gray-500 text-xs relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50">
                    <span v-html="box_stats?.customer.addresses.delivery.formatted_address"></span>
                    <div @click="() => isModalAddress = true"
                        class="whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                        <span>{{ trans('Edit') }}</span>
                    </div>
                </dd>
            </div>
        </BoxStatPallet>

        <!-- Box: Product stats -->
        <BoxStatPallet class="py-4 pl-1.5 pr-3" icon="fal fa-user">
            <div class="relative flex items-start w-full flex-none gap-x-1">
                <dt class="flex-none pt-0.5">
                    <FontAwesomeIcon icon='fal fa-dollar-sign' fixed-width aria-hidden='true' class="text-gray-500" />
                </dt>

                <NeedToPay
                    @click="() => box_stats.products.payment.pay_amount > 0 ? (isOpenModalPayment = true, fetchPaymentMethod()) : false"
                    :totalAmount="box_stats.products.payment.total_amount"
                    :paidAmount="box_stats.products.payment.paid_amount"
                    :payAmount="box_stats.products.payment.pay_amount"
                    :class="[box_stats.products.payment.pay_amount ? 'hover:bg-gray-100 cursor-pointer' : '']"
                    :currencyCode="currency.code" />
            </div>

            <div class="mt-1 flex items-center w-full flex-none gap-x-1.5">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-weight' fixed-width aria-hidden='true' class="text-gray-500" />
                </dt>
                <dd class="text-gray-500 sep" v-tooltip="trans('Estimated weight of all products')">
                    {{ box_stats?.products.estimated_weight || 0 }} kilograms
                </dd>
            </div>


            <div v-if="delivery_note" class="mt-1 flex items-center w-full flex-none justify-between">
                <Link
                    :href="route(routes.delivery_note.deliveryNoteRoute.name, routes.delivery_note.deliveryNoteRoute.parameters)"
                    class="flex items-center gap-3 gap-x-1.5 primaryLink cursor-pointer">
                <dt class="flex-none">
                    <FontAwesomeIcon icon='fal fa-truck' fixed-width aria-hidden='true' class="text-gray-500" />
                </dt>
                <dd class="text-gray-500 " v-tooltip="trans('Delivery Note')">
                    {{ delivery_note?.reference }}
                </dd>
                </Link>
                <a :href="route(routes.delivery_note.deliveryNotePdfRoute.name, routes.delivery_note.deliveryNotePdfRoute.parameters)"
                    as="a" target="_blank" class="flex items-center">
                    <button class="flex items-center">
                        <div class="flex-none">
                            <FontAwesomeIcon :icon="faFilePdf" fixed-width aria-hidden="true"
                                class="text-gray-500 hover:text-indigo-500 transition-colors duration-200" />
                        </div>
                    </button>
                </a>
            </div>

        </BoxStatPallet>

        <!-- Box: Order summary -->
        <BoxStatPallet class="col-span-2 border-t lg:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
                <!-- <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2> -->

                <OrderSummary :order_summary="box_stats.order_summary" :currency_code="currency.code"  />

                <!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </BoxStatPallet>
    </div>

    <Tabs  v-if="currentTab != 'products'" :current="currentTab" :navigation="tabs?.navigation" @update:tab="handleTabUpdate" />

    <div class="pb-12">
        <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab"
            :updateRoute="routes.updateOrderRoute" :state="data?.data?.state"
            :detachRoute="attachmentRoutes.detachRoute" 
            :fetchRoute="routes.products_list"
			:modalOpen="isModalUploadOpen"
			:action="currentAction"
			@update:tab="handleTabUpdate"/>
    </div>

	<ModalProductList v-model="isModaProductListOpen" :fetchRoute="routes.products_list" :action="currentAction" :current="currentTab"  v-model:currentTab="currentTab" :typeModel="'order'" />

    <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
        <ModalAddress
            :addresses="addresses"
            :updateRoute="address_update_route"
            keyPayloadEdit="delivery_address"
        />
    </Modal>


    <Modal :isOpen="isOpenModalPayment" @onClose="isOpenModalPayment = false" width="w-[600px]">
        <div class="isolate bg-white px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-lg font-bold tracking-tight sm:text-2xl">{{ trans('Invoice Payment') }}</h2>
                <p class="text-xs leading-5 text-gray-400">
                    {{ trans('Information about payment from customer') }}
                </p>
            </div>

            <div class="mt-7 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <div class="col-span-2">
                    <label for="first-name" class="block text-sm font-medium leading-6">
                        <span class="text-red-500">*</span> {{ trans('Select payment method') }}
                    </label>
                    <div class="mt-1">
                        <PureMultiselect v-model="paymentData.payment_method" :options="listPaymentMethod"
                            :isLoading="isLoadingFetch" label="name" valueProp="id" required caret />
                    </div>
                </div>

                <div class="col-span-2">
                    <label for="last-name" class="block text-sm font-medium leading-6">
                        {{ trans('Payment amount') }}
                    </label>
                    <div class="mt-1">
                        <PureInputNumber v-model="paymentData.payment_amount" />
                    </div>
                    <div class="space-x-1">
                        <span class="text-xxs text-gray-500">{{ trans('Need to pay') }}: {{
                            locale.currencyFormat(box_stats.order_summary.currency.code || 'usd',
                                box_stats.products.payment.pay_amount) }}</span>
                        <Button @click="() => paymentData.payment_amount = box_stats.products.payment.pay_amount"
                            :disabled="paymentData.payment_amount === box_stats.products.payment.pay_amount"
                            type="tertiary" label="Pay all" size="xxs" />
                    </div>
                </div>

                <div class="col-span-2">
                    <label for="last-name" class="block text-sm font-medium leading-6">{{ trans('Reference') }}</label>
                    <div class="mt-1">
                        <PureInput v-model="paymentData.payment_reference" placeholder="#000000" />
                    </div>
                </div>

                <!-- <div class="col-span-2">
                    <label for="message" class="block text-sm font-medium leading-6">Note</label>
                    <div class="mt-1">
                        <PureTextarea
                            v-model="paymentData.payment_reference"
                            name="message"
                            id="message" rows="4"
                        />
                    </div>
                </div> -->
            </div>

            <div class="mt-6 mb-4 relative">
                <Button @click="() => onSubmitPayment()" label="Submit" :disabled="!(!!paymentData.payment_method)"
                    :loading="isLoadingPayment" full />
                <Transition name="spin-to-down">
                    <p v-if="errorPaymentMethod" class="absolute text-red-500 italic text-sm mt-1">*{{
                        errorPaymentMethod }}</p>
                </Transition>
            </div>
        </div>
    </Modal>

    <UploadAttachment v-model="isModalUploadOpen" scope="attachment" :title="{
        label: 'Upload your file',
        information: 'The list of column file: customer_reference, notes, stored_items'
    }" progressDescription="Adding Pallet Deliveries" :attachmentRoutes="attachmentRoutes" />
</template>

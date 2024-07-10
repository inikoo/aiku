<!--
  -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { inject, onMounted, ref } from 'vue'
import { capitalize } from '@/Composables/capitalize'
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'


import { PalletReturn, BoxStats } from '@/types/Pallet'
import { Link, router } from '@inertiajs/vue3'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import PureAddress from '@/Components/Pure/PureAddress.vue'
import { trans } from 'laravel-vue-i18n'

import Modal from '@/Components/Utils/Modal.vue'
import { routeType } from '@/types/route'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import Button from '@/Components/Elements/Buttons/Button.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faQuestionCircle, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faQuestionCircle, faPencil)

const props = defineProps<{
    dataPalletReturn: PalletReturn
    boxStats: BoxStats
    updateRoute: routeType
}>()

const locale = inject('locale', {})


onMounted(() => {
    JsBarcode('#palletReturnBarcode', 'par-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 50,
        displayValue: false
    })
})

// Method: Create new address
const isModalAddress = ref(false)
const isSubmitAddressLoading = ref(false)
const onSubmitNewAddress = async () => {
    // console.log(props.boxStats.fulfilment_customer.address.value)
    const filterDataAdddress = {...props.boxStats.fulfilment_customer.address.value}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.id  // Remove id cuz create new one

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            address: filterDataAdddress
        },
        {
            preserveScroll: true,
            onStart: () => isSubmitAddressLoading.value = true,
            onFinish: () => {
                isSubmitAddressLoading.value = false,
                isModalAddress.value = false
            },
            onError: () => notify({
                title: "Failed",
                text: "Failed to update the address, try again.",
                type: "error",
            })
        }
    )

}

// Method: Edit address history
const isEditAddress = ref(false)
const selectedEditableAddress = ref(null)
const onEditAddress = (address: {}) => {
    isEditAddress.value = true
    selectedEditableAddress.value = {...address}
}
const onSubmitEditAddress = () => {
    // console.log(props.boxStats.fulfilment_customer.address.value)
    const filterDataAdddress = {...selectedEditableAddress.value}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.country_code

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            address: filterDataAdddress
        },
        {
            preserveScroll: true,
            onStart: () => isSubmitAddressLoading.value = true,
            onFinish: () => {
                isSubmitAddressLoading.value = false
                // isModalAddress.value = false
            },
            onError: () => notify({
                title: "Failed",
                text: "Failed to update the address, try again.",
                type: "error",
            })
        }
    )
}

// Method: Select address history
const selectedOptions = [{label: 'Create new', value: 'createNew'}, {label: 'Select from saved', value: 'selectSaved'}]
const selectedCreateOrSelect = ref('selectSaved')
const isSelectAddressLoading = ref<number | boolean>(false)
const onSelectAddress = (selectedAddress) => {

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            delivery_address_id: selectedAddress.id
        },
        {
            onStart: () => isSelectAddressLoading.value = selectedAddress.id,
            onFinish: () => isSelectAddressLoading.value = false
        }
    )
    // props.boxStats.fulfilment_customer.address.value = selectedAddress
}
</script>

<template>
    <div class="h-min grid sm:grid-cols-2 lg:grid-cols-4 border-t border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Customer -->
        <BoxStatPallet class="py-1 sm:py-2 px-3">
            <!-- Field: Reference -->
            <Link as="a" v-if="boxStats.fulfilment_customer.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, boxStats.fulfilment_customer.fulfilment.slug, boxStats.fulfilment_customer.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Reference</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="boxStats.fulfilment_customer.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="boxStats.fulfilment_customer.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="boxStats.fulfilment_customer?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500 w-full pr-4 break-words leading-none">
                    {{ boxStats.fulfilment_customer?.customer.email }}
                </dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="boxStats.fulfilment_customer?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.fulfilment_customer?.customer.phone }}</dd>
            </div>

            <!-- Field: Delivery Address -->
            <div class="flex items-start w-full flex-none gap-x-2">
                <dt v-tooltip="`Pallet Return's address`" class="flex-none">
                    <span class="sr-only">Delivery address</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>

                <dd v-if="dataPalletReturn.delivery_address" class="text-xs text-gray-500">
                    <div class="relative px-2 py-1 ring-1 ring-gray-300 rounded bg-gray-50">
                        <span class="" v-html="dataPalletReturn.delivery_address.formatted_address" />

                        <div @click="() => isModalAddress = true"
                            class="whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                            <!-- <FontAwesomeIcon icon='fal fa-pencil' size="sm" class='mr-1' fixed-width aria-hidden='true' /> -->
                            <span>Edit</span>
                        </div>
                    </div>
                </dd>
                <div v-else @click="() => isModalAddress = true" class="text-xs inline whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                    <span>Setup delivery address</span>
                </div>
            </div>
        </BoxStatPallet>

        <!-- Box Stats: 2 -->
        <BoxStatPallet class="py-1 sm:py-2 px-3" :label="capitalize(dataPalletReturn.state)" icon="fal fa-truck-couch">
            <div class="mt-2 mb-4 h-full w-full py-1 px-2 flex flex-col bg-gray-100 ring-1 ring-gray-300 rounded items-center">
                <svg id="palletReturnBarcode" class="w-full h-full" />
                <div class="text-xxs md:text-xxs text-gray-500">
                    par-{{ route().params.palletReturn }}
                </div>
            </div>

            <div class="flex items-center flex-none gap-x-2 w-fit" v-tooltip="trans('Delivery status')">
                <dt class="flex-none">
                    <span class="sr-only">{{ boxStats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='boxStats.delivery_status.icon' :class='boxStats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ boxStats.delivery_status.tooltip }}</dd>
            </div>

            <!-- Section: Pallets, Services, Physical Goods -->
            <div class="border-t border-gray-300 mt-2 pt-2 space-y-0.5">
                <div v-tooltip="trans('Count of pallets')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-sm font-medium tabular-nums">{{ dataPalletReturn.number_pallets }} <span class="text-gray-400 font-normal">{{ dataPalletReturn.number_pallets > 1 ? trans('Pallets') : trans('Pallet') }}</span></dd>
                </div>
                <div v-tooltip="trans('Count of stored items')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-sm font-medium tabular-nums">{{ dataPalletReturn.number_stored_items }} <span class="text-gray-400 font-normal">{{ dataPalletReturn.number_pallets > 1 ? trans('Stored items') : trans('Stored item') }}</span></dd>
                </div>
                <div v-tooltip="trans('Count of services')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-concierge-bell' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-sm font-medium tabular-nums">{{ dataPalletReturn.number_services }} <span class="text-gray-400 font-normal">{{ dataPalletReturn.number_pallets > 1 ? trans('Services') : trans('Service') }}</span></dd>
                </div>
                <div v-tooltip="trans('Count of physical goods')" class="w-fit flex items-center gap-x-3">
                    <dt class="flex-none">
                        <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500 text-sm font-medium tabular-nums">{{ dataPalletReturn.number_physical_goods }} <span class="text-gray-400 font-normal">{{ dataPalletReturn.number_pallets > 1 ? trans('Physical goods') : trans('Physical good') }}</span></dd>
                </div>
            </div>
        </BoxStatPallet>


        <!-- Box: Order summary -->
        <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
            <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
                <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2>

                <dl class="mt-2 space-y-2 text-gray-600">
                    <div class="flex flex-col gap-y-2">
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm">{{ trans('Pallets')}}</dt>
                            <dd class="place-self-end text-sm">{{ locale.number(boxStats.order_summary?.number_pallets || 0)}}</dd>
                            <dd class="place-self-end text-sm">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.pallets_price || 0) }}</dd>
                            <dd class="place-self-end text-sm font-medium">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.total_pallets_price || 0) }}</dd>
                        </div>
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm">{{ trans('Services') }}</dt>
                            <dd class="place-self-end text-sm">{{ locale.number(boxStats.order_summary?.number_services || 0) }}</dd>
                            <dd class="place-self-end text-sm">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.services_price || 0) }}</dd>
                            <dd class="place-self-end text-sm font-medium">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.total_services_price || 0) }}</dd>
                        </div>
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm">{{ trans('Physical Goods') }}</dt>
                            <dd class="place-self-end text-sm">{{ locale.number(boxStats.order_summary?.number_physical_goods || 0) }}</dd>
                            <dd class="place-self-end text-sm">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.physical_goods_price || 0) }}</dd>
                            <dd class="place-self-end text-sm font-medium">{{ locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary?.total_physical_goods_price || 0) }}</dd>
                        </div>
                    </div>

                    <!-- Field: Shipping estimate & Tax estimate -->
                    <div class="flex flex-col justify-center gap-y-2 border-t border-gray-200 pt-2">
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center text-sm text-gray-600">
                                <span>Shipping estimate</span>
                                <FontAwesomeIcon icon='fal fa-question-circle' v-tooltip="boxStats.order_summary.shipping.tooltip" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                            </dt>
                            <dd :class="boxStats.order_summary.shipping.fee ? '' : 'text-green-600 animate-pulse'" class="text-sm">{{ boxStats.order_summary.shipping.fee ? locale.currencyFormat(boxStats.order_summary.currency_code, boxStats.order_summary.shipping.fee) : 'Free' }}</dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="flex items-center text-sm text-gray-600">
                                <span>Tax estimate</span>
                                <FontAwesomeIcon icon='fal fa-question-circle' v-tooltip="boxStats.order_summary.tax.tooltip" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-sm font-medium">{{ boxStats.order_summary.tax.fee }}</dd>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-200 pt-3">
                        <dt class="text-base font-medium">Order total</dt>
                        <dd class="text-base font-medium">{{ boxStats.order_summary.total_price }}</dd>
                    </div>
                </dl>

                <!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </BoxStatPallet>
    </div>

    <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
		<div class="h-[500px] px-2 overflow-auto">
            <div class="text-2xl font-bold text-center mb-8">
                Edit customer's address
            </div>

            <div class="grid grid-cols-2 gap-x-8 ">
                <div class="col-span-2 flex items-center justify-center mb-4 gap-x-2 text-sm">
                    <!-- <div @click="selectedCreateOrSelect = 'createNew'"
                        class="px-1 select-none cursor-pointer whitespace-nowrap"
                        :class="selectedCreateOrSelect === 'createNew' ? 'text-indigo-600' : 'text-gray-400'">
                        xxx
                    </div> -->
                    <div class="border border-indigo-300 w-fit rounded-full overflow-hidden">
                        <RadioGroup v-model="selectedCreateOrSelect" class="grid grid-cols-2">
                            <RadioGroupOption v-for="curr in selectedOptions" as="template" :key="curr.value" :value="curr.value" v-slot="{ active, checked }">
                                <div class="select-none cursor-pointer focus:outline-none flex items-center justify-center py-2 px-3 text-xs font-semibold uppercase sm:flex-1"
                                    :class="[checked ? 'bg-indigo-200 hover:bg-indigo-300' : 'bg-white hover:bg-indigo-50']">
                                    {{ curr.label }}
                                </div>
                            </RadioGroupOption>
                        </RadioGroup>
                    </div>
                    <!-- <div @click="selectedCreateOrSelect = 'selectSaved'"
                        class="select-none cursor-pointer whitespace-nowrap"
                        :class="selectedCreateOrSelect === 'selectSaved' ? 'text-indigo-600' : 'text-gray-400'">
                        xx
                    </div> -->
                </div>

                <div v-if="selectedCreateOrSelect === 'createNew'" class="relative p-3">
                    <PureAddress v-model="boxStats.fulfilment_customer.address.value"
                        :options="boxStats.fulfilment_customer.address.options"
                        @update:modelValue="() => boxStats.fulfilment_customer.address.value.id = null"
                    />
                    <div class="mt-6 flex justify-center">
                        <Button @click="() => onSubmitNewAddress()" label="Create new and select" :loading="isSubmitAddressLoading" full />
                    </div>

                    <!-- <Transition>
                        <div class="absolute inset-0 bg-black/30 text-white text-lg rounded-md grid place-content-center">
                            Not editable
                        </div>
                    </Transition> -->
                </div>

                <div v-if="selectedCreateOrSelect === 'selectSaved'" class="col-span-2 relative p-4 h-fit">
                    <div class="font-medium mb-4">
                        Saved address:
                    </div>

                    <!-- Saved Address: list -->
                    <template v-if="boxStats.fulfilment_customer.addresses_list.data?.length">
                        <div class="grid grid-cols-2 gap-x-4">
                            <div class="grid gap-x-2 gap-y-4 h-fit transition-all" :class="[ isEditAddress ? '' : 'col-span-2 grid-cols-2' ]">
                                <div
                                    v-for="(address, idxAddress) in boxStats.fulfilment_customer.addresses_list.data"
                                    :key="idxAddress + address.id"
                                    class="relative text-xs ring-1 ring-gray-300 rounded-lg px-5 py-3 h-fit transition-all"
                                    :class="[
                                        boxStats.fulfilment_customer.address.value.id == address.id ? 'bg-indigo-50' : '',
                                        selectedEditableAddress?.id == address.id ? 'ring-2 ring-offset-4 ring-indigo-500' : ''
                                    ]"
                                >
                                    <div v-html="address.formatted_address"></div>
                                    <div class="flex items-center gap-x-1 absolute top-2 right-2">
                                        <!-- <Button
                                            size="xxs"
                                            label="Edit"
                                            :key="address.id + '-' + selectedEditableAddress?.id"
                                            :type="selectedEditableAddress?.id == address.id ? 'primary' : 'tertiary'"

                                        /> -->
                                        <div @click="() => onEditAddress(address)" class="inline cursor-pointer" :class="[selectedEditableAddress?.id == address.id ? 'underline' : 'hover:underline']">
                                            Edit
                                        </div>

                                        <Transition>
                                            <div v-if="boxStats.fulfilment_customer.address.value.id == address.id"
                                                v-tooltip="'Selected as pallet return address'"
                                                class="bg-indigo-500/80 text-white cursor-default rounded px-1.5 py-1 leading-none text-xxs"
                                            >
                                                Selected
                                            </div>
                                            <Button
                                                v-else
                                                @click="() => onSelectAddress(address)"
                                                :label="isSelectAddressLoading == address.id ? '' : 'Select'"
                                                size="xxs"
                                                type="tertiary"
                                                :loading="isSelectAddressLoading == address.id"/>
                                            <!-- <span>Select</span> -->
                                        </Transition>
                                    </div>
                                </div>
                            </div>

                            <div v-if="isEditAddress" class="relative bg-gray-100 p-4 rounded-md">
                                <div @click="() => (isEditAddress = false, selectedEditableAddress = null)" class="absolute top-2 right-2 cursor-pointer">
                                    <FontAwesomeIcon icon='fal fa-times' class='text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                                </div>

                                <PureAddress v-model="selectedEditableAddress"
                                    :options="boxStats.fulfilment_customer.address.options"
                                    @update:modelValue="() => boxStats.fulfilment_customer.address.value.id = null"
                                />

                                <div class="mt-6 flex justify-center">
                                    <Button @click="() => onSubmitEditAddress()" label="Edit address" :loading="isSubmitAddressLoading" full />
                                </div>
                            </div>
                        </div>
                    </template>

                    <div v-else class="text-sm flex items-center justify-center h-3/4 font-medium text-center text-gray-400">
                        No address history found
                    </div>

                    <!-- <Transition>
                        <div class="absolute top-0 inset-0 bg-black/30 text-white text-lg rounded-md grid place-content-center">
                            Not editable
                        </div>
                    </Transition> -->
                </div>
            </div>


            <!-- {{ boxStats.fulfilment_customer.address.value }} -->
		</div>
	</Modal>
</template>

<style scoped lang="scss">
:deep(.country) {
    @apply font-bold text-sm
}

</style>

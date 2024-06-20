<!--
  -  Author: Vika Aqordi <aqordivika@yahoo.co.id>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import JsBarcode from 'jsbarcode'
import { onMounted, ref } from 'vue'
import { capitalize } from '@/Composables/capitalize'

import { PalletReturn, BoxStats } from '@/types/Pallet'
import { Link } from '@inertiajs/vue3'
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


onMounted(() => {
    JsBarcode('#palletReturnBarcode', 'par-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 50,
        displayValue: false
    })
})

// Method: Submit Address
const isModalAddress = ref(false)
const isSubmitAddressLoading = ref(false)
const onSubmitAddress = async () => {
    isSubmitAddressLoading.value = true
    try {
        const response = await axios.patch(route(props.updateRoute.name, props.updateRoute.parameters), {
            address: props.boxStats.fulfilment_customer.address.value
        })
        console.log('response', response)

    } catch (error) {
        console.log('error', error)
        notify({
			title: "Failed",
			text: "Failed to update the address, try again.",
			type: "error",
		})
    }

    isSubmitAddressLoading.value = false
    isModalAddress.value = false
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
            <div class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Delivery address</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd v-if="dataPalletReturn.delivery_address" class="text-xs text-gray-500">
                    <span class="mr-2" v-html="dataPalletReturn.delivery_address.formatted_address"></span>
                    <div @click="() => isModalAddress = true" class="inline whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                        <FontAwesomeIcon icon='fal fa-pencil' size="sm" class='mr-1' fixed-width aria-hidden='true' />
                        <span>Edit</span>
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

                <dl class="mt-2 space-y-2">
                    <div class="flex flex-col gap-y-2">
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm text-gray-600">Pallets</dt>
                            <dd class="place-self-end text-sm">{{ boxStats.order_summary.number_pallets}}</dd>
                            <dd class="place-self-end text-sm">@4.25</dd>
                            <dd class="place-self-end text-sm font-medium">$99.00</dd>
                        </div>
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm text-gray-600">Services</dt>
                            <dd class="place-self-end text-sm">{{ boxStats.order_summary.number_services}}</dd>
                            <dd class="place-self-end text-sm">@4.25</dd>
                            <dd class="place-self-end text-sm font-medium">{{ boxStats.order_summary.total_services_price }}</dd>
                        </div>
                        <div class="grid grid-cols-4 gap-x-4 items-center justify-between">
                            <dt class="text-sm text-gray-600">Physical Goods</dt>
                            <dd class="place-self-end text-sm">{{ boxStats.order_summary.number_physical_goods}}</dd>
                            <dd class="place-self-end text-sm">@4.25</dd>
                            <dd class="place-self-end text-sm font-medium">{{ boxStats.order_summary.total_physical_goods_price }}</dd>
                        </div>
                    </div>

                    <!-- Field: Shipping estimate & Tax estimate -->
                    <div class="flex flex-col justify-center gap-y-2 border-t border-gray-200 pt-2">
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center text-sm text-gray-600">
                                <span>Shipping estimate</span>
                                <FontAwesomeIcon icon='fal fa-question-circle' v-tooltip="'Estimated Shipping'" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-sm text-green-600 animate-pulse">Free</dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="flex items-center text-sm text-gray-600">
                                <span>Tax estimate</span>
                                <FontAwesomeIcon icon='fal fa-question-circle' v-tooltip="'Tax estimate'" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-sm font-medium">$8.32</dd>
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
            <div class="grid grid-cols-2 gap-x-4">
            <!-- <pre>{{ boxStats.fulfilment_customer.address.value }}</pre> -->
                <PureAddress v-model="boxStats.fulfilment_customer.address.value" :options="boxStats.fulfilment_customer.address.options" />
                <div class="bg-gray-100 ring-1 ring-gray-300 rounded-lg px-6 pt-4 pb-6 h-fit">
                    <div class="font-bold text-lg">India</div>
                    <div>
                        Address Line 1
                    </div>
                    <div>
                        Address Line 2
                    </div>
                    <div>
                        Sorting code, Locality, Dependant Locality
                    </div>
                    <div>
                        Administrative area, Postal Code
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-center">
                <Button @click="() => onSubmitAddress()" label="Submit" :loading="isSubmitAddressLoading" />
            </div>
            <!-- {{ boxStats.fulfilment_customer.address.value }} -->
		</div>
	</Modal>
</template>

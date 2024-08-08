

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 02 Apr 2024 20:10:35 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { inject, ref } from 'vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import CustomerShowcaseStats from '@/Components/Showcases/Grp/CustomerShowcaseStats.vue'

import { routeType } from '@/types/route'
import { FulfilmentCustomerStats } from '@/types/Pallet'
import { trans } from 'laravel-vue-i18n'
import TabSelector from '@/Components/Elements/TabSelector.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Link } from '@inertiajs/vue3'
import Tag from '@/Components/Tag.vue'


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLink, faLongArrowRight } from '@far'
import { faSync, faCalendarAlt, faEnvelope, faPhone, faChevronRight, faExternalLink, faMapMarkerAlt, faAddressCard } from '@fal'
import Modal from '@/Components/Utils/Modal.vue'
import { AddressManagement } from '@/types/PureComponent/Address'
import ModalAddress from '@/Components/Utils/ModalAddress.vue'
library.add(faLink, faSync, faCalendarAlt, faEnvelope, faPhone, faChevronRight, faExternalLink, faMapMarkerAlt, faAddressCard, faLongArrowRight)

const props = defineProps<{
    data: {
        addresses: AddressManagement
        address_update_route: routeType
        // customer: PalletCustomer
        fulfilment_customer: {
            radioTabs: {
                [key: string]: boolean
            }
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number
            customer: {}
        }
        updateRoute: routeType
        stats: {
            [key: string]: FulfilmentCustomerStats
        }
        warehouse_summary: {
            [key: string]: number
        }
        webhook: {
            webhook_access_key: string | null
            domain: string
            route: routeType
        }
        rental_agreement: {
            stats?: {
                data: {
                    id: number
                    slug: string
                    reference: string
                    state: string
                    billing_cycle: string
                    pallets_limit: number
                    route: routeType
                }
            }
            createRoute: routeType
        }
        recurring_bill: {
            route: routeType
            status: string  // 'former' and 'current'
            start_date: string
            end_date: string
            total: number
            currency_code: string
        }
    },
    tab: string
}>()

const locale = inject('locale', {})

// Tabs radio: v-model
const radioValue = ref<string[]>(Object.keys(props.data.fulfilment_customer.radioTabs).filter(key => props.data.fulfilment_customer.radioTabs[key]))

// Tabs radio: options
const optionRadio = [
    {
        value: 'pallets_storage',
        label: 'Pallet Storage'
    },
    {
        value: 'items_storage',
        label: 'Items Storage'
    },
    {
        value: 'dropshipping',
        label: 'Dropshipping'
    },
]

const isLoadingButtonRentalAgreement = ref(false)
const isLoading = ref<string | boolean>(false)
const isModalAddress = ref(false)
</script>

<template>
    <!-- Section: Stats box -->
    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-3">

        <div class="space-y-3">
            <!-- Section: Radio -->
            
            <div class="space-y-3 relative">
                <!-- Section: Profile box -->
                <!-- <Transition name="headlessui" mode="out-in"> -->
                    <div class="col-span-2 grid ">
                        <div class="w-full">
                            <div class="rounded-lg shadow-sm ring-1 ring-gray-300">
                                <dl class="flex flex-wrap">
                                    <!-- Profile: Header -->
                                    <!-- <div class="flex w-full py-6">
                                        <div class="flex-auto pl-6">
                                            <dt class="text-sm font-semibold leading-6 text-gray-900">Total Clients</dt>
                                            <dd class="mt-1 text-base font-semibold leading-6 text-gray-900">{{ data.customer.number_current_clients || 0 }}</dd>
                                        </div>
                                        <div class="flex-none self-end px-6 pt-4">
                                            <dt class="sr-only">Reference</dt>
                                            <dd class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                {{ data.customer.reference }}
                                            </dd>
                                        </div>
                                    </div> -->
                                    <!-- Section: Field -->
                                    <div class="flex flex-col gap-y-2 w-full py-6">

                                        <!-- Field: Contact name -->
                                        <div v-if="data.fulfilment_customer.customer.contact_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                                            <dt v-tooltip="'Contact name'" class="flex-none">
                                                <span class="sr-only">Contact name</span>
                                                <FontAwesomeIcon icon='fal fa-address-card' class='text-gray-400' fixed-width aria-hidden='true' />
                                            </dt>
                                            <dd class="text-gray-500">{{ data.fulfilment_customer.customer.contact_name }}</dd>
                                        </div>

                                        <!-- Field: Company name -->
                                        <div v-if="data.fulfilment_customer.customer.company_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                                            <dt v-tooltip="'Company name'" class="flex-none">
                                                <span class="sr-only">Company name</span>
                                                <FontAwesomeIcon icon='fal fa-building' class='text-gray-400' fixed-width aria-hidden='true' />
                                            </dt>
                                            <dd class="text-gray-500">{{ data.fulfilment_customer.customer.company_name }}</dd>
                                        </div>

                                        <!-- Field: Email -->
                                        <div v-if="data.fulfilment_customer.customer?.email" class="flex items-center w-full flex-none gap-x-4 px-6">
                                            <dt v-tooltip="'Email'" class="flex-none">
                                                <span class="sr-only">Email</span>
                                                <FontAwesomeIcon icon='fal fa-envelope' class='text-gray-400' fixed-width aria-hidden='true' />
                                            </dt>
                                            <a :href="`mailto:${data.fulfilment_customer.customer?.email}`" v-tooltip="'Click to send email'" class="text-gray-500 hover:text-gray-700">{{ data.fulfilment_customer.customer?.email }}</a>
                                        </div>
                                        
                                        <!-- Field: Phone -->
                                        <div v-if="data.fulfilment_customer.customer?.phone" class="flex items-center w-full flex-none gap-x-4 px-6">
                                            <dt v-tooltip="'Phone'" class="flex-none">
                                                <span class="sr-only">Phone</span>
                                                <FontAwesomeIcon icon='fal fa-phone' class='text-gray-400' fixed-width aria-hidden='true' />
                                            </dt>
                                            <a :href="`tel:${data.fulfilment_customer.customer?.phone}`" v-tooltip="'Click to make a phone call'" class="text-gray-500 hover:text-gray-700">{{ data.fulfilment_customer.customer?.phone }}</a>
                                        </div>

                                        <!-- Field: Address -->
                                        <div v-if="data.fulfilment_customer.customer?.address" class="flex items w-full flex-none gap-x-4 px-6">
                                            <dt v-tooltip="'Address'" class="flex-none">
                                                <span class="sr-only">Address</span>
                                                <FontAwesomeIcon icon='fal fa-map-marker-alt' class='text-gray-400' fixed-width aria-hidden='true' />
                                            </dt>
                                            <dd v-if="data.fulfilment_customer.customer?.address" class="w-full text-gray-500">
                                                <div class="relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50">
                                                    <span class="" v-html="data.fulfilment_customer.customer?.address.formatted_address" />

                                                    <div @click="() => isModalAddress = true"
                                                        class="whitespace-nowrap select-none text-gray-500 hover:text-blue-600 underline cursor-pointer">
                                                        <!-- <FontAwesomeIcon icon='fal fa-pencil' size="sm" class='mr-1' fixed-width aria-hidden='true' /> -->
                                                        <span>Edit</span>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                <!-- </Transition> -->

                <!-- Box Group: Pallets -->
                <CustomerShowcaseStats v-if="data?.rental_agreement?.stats" :stats="data.stats" />
                <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
                    <ModalAddress
                        :addresses="data.addresses"
                        :updateRoute="data.address_update_route"
                    />
                </Modal>
            </div>
        </div>

        <!-- Section: -->
        <div class="w-full space-y-4">
            <TabSelector :optionRadio="optionRadio" :radioValue="radioValue" :updateRoute="data.updateRoute"/>
            
            <div class="border-t border-gray-200 pt-4 w-full max-w-full">
                <!-- Section: Recurring Bills -->
                <div v-if="data.recurring_bill" class="block group relative w-full gap-x-2 border border-gray-300 px-4 py-4 rounded-lg mb-4">
                    <!-- <FontAwesomeIcon icon='fal fa-receipt' class='text-3xl text-gray-400' fixed-width aria-hidden='true' /> -->
                    <div class="border-l-4 border-indigo-500 pl-2 leading-none text-lg">
                        <div class="block text-lg font-semibold">Recurring Bills</div>
                        <div class="text-sm flex items-center gap-x-1">
                            {{ locale.currencyFormat(data.recurring_bill.currency_code, data.recurring_bill.total || 0) }}
                        </div>
                    </div>

                    <!-- State Date & End Date -->
                    <div class="pl-1 mt-4 w-80 lg:w-96 grid grid-cols-9 gap-x-3">
                        <div class="col-span-4 text-sm">
                            <div class="text-gray-400">Start date</div>
                            <div class="font-medium">{{ useFormatTime(data.recurring_bill.start_date) }}</div>
                        </div>

                        <div class="flex justify-center items-center">
                            <FontAwesomeIcon icon='fal fa-chevron-right' class='text-xs' fixed-width aria-hidden='true' />
                        </div>

                        <div class="col-span-4 text-sm">
                            <div class="text-gray-400">End date</div>
                            <div class="font-medium">{{ useFormatTime(data.recurring_bill.end_date) }}</div>
                        </div>
                    </div>

                    <div class="pl-1 mt-6 w-full flex items-end justify-between">
                        <div class="flex h-fit">
                            <Tag :theme="data.recurring_bill.status === 'current' ? 3 : undefined" size="xxs">
                                <template #label>
                                    <FontAwesomeIcon v-if="data.recurring_bill.status === 'current'" icon='fas fa-circle' class='text-green-500 animate-pulse text-[7px]' fixed-width aria-hidden='true' />
                                    <span class="capitalize">{{ data.recurring_bill.status === 'current' ? 'Active' : 'Past' }}</span>
                                </template>
                            </Tag>
                        </div>
                        
                        <Link :href="route(data.recurring_bill.route.name, data.recurring_bill.route.parameters)"
                            @start="() => isLoading = 'loadingVisitRecurring'"
                            @error="() => isLoading = false"
                        >
                            <Button
                                :type="'tertiary'"
                                :loading="isLoading === 'loadingVisitRecurring'"
                                size="s"
                                label="See details"
                                iconRight="fal fa-external-link"
                            />
                        </Link>
                    </div>
                </div>
                
                <!-- Section: Rental Agreement -->
                <div class="rounded-lg ring-1 ring-gray-300">
                    <div class="border-b border-gray-300 py-2 px-2  pl-4 flex items-center justify-between">
                        <div class="">{{ trans('Rental Agreement') }} <span class="text-gray-400 text-sm">#{{ data.rental_agreement.stats?.data?.reference }}</span></div>
                        <Link v-if="data.rental_agreement.stats" :href="route(data.rental_agreement.stats?.data?.route.name, data.rental_agreement.stats?.data?.route.parameters)" @start="() => isLoadingButtonRentalAgreement = true" @cancel="() => isLoadingButtonRentalAgreement = false">
                            <Button type="edit" :loading="isLoadingButtonRentalAgreement"/>
                        </Link>
                    </div>
                    
                    <!-- Stats -->
                    <div v-if="data.rental_agreement.stats" class="p-5 space-y-2">
                        <div class="flex gap-x-1 items-center text-sm">
                            <div class="">{{ trans('Created at') }}:</div>
                            <div class="text-gray-500">{{ useFormatTime(data.customer?.created_at) }}</div>
                        </div>
                        <div class="flex gap-x-1 items-center text-sm">
                            <div class="">{{ trans('Billing Cycle') }}:</div>
                            <div class="text-gray-500 capitalize">{{ data.rental_agreement.stats?.data.billing_cycle }}</div>
                        </div>
                        <div class="flex gap-x-1 items-center text-sm">
                            <div class="">{{ trans('Pallet Limit') }}:</div>
                            <div class="text-gray-500">{{ data.rental_agreement.stats?.data.pallets_limit || `(${trans('No limit')})` }}</div>
                        </div>
                    </div>
            
                    <div v-else class="text-center py-16">
                        <div class="text-gray-500 text-xs mb-1">The rental agreement is not created yet.</div>
                        <Link :href="route(data.rental_agreement.createRoute.name, data.rental_agreement.createRoute.parameters)" @start="() => isLoadingButtonRentalAgreement = true" @cancel="() => isLoadingButtonRentalAgreement = false">
                            <Button type="secondary" label="Create Rental Agreement" :loading="isLoadingButtonRentalAgreement"/>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


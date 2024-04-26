

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 02 Apr 2024 20:10:35 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from 'vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { useCopyText } from '@/Composables/useCopyText'
import CustomerShowcaseStats from '@/Components/Showcases/Grp/CustomerShowcaseStats.vue'

import { routeType } from '@/types/route'
import { PalletCustomer, PieCustomer } from '@/types/Pallet'
import { trans } from 'laravel-vue-i18n'
import TabSelector from '@/Components/Elements/TabSelector.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLink} from '@far'
import { faSync, faCalendarAlt, faEnvelope, faPhone } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import BoxNote from '@/Components/Pallet/BoxNote.vue'
import { Link } from '@inertiajs/vue3'
library.add(faLink, faSync, faCalendarAlt, faEnvelope, faPhone)

const props = defineProps<{
    data: {
        customer: PalletCustomer
        fulfilment_customer: {
            radioTabs: {
                [key: string]: boolean
            }
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number
        }
        updateRoute: routeType
        pieData: {
            [key: string]: PieCustomer
        }
        webhook: {
            webhook_access_key: string | null
            domain: string
            route: routeType
        }
        rental_agreement: {
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
    },
    tab: string
}>()

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

const isLoadingEditAgreement = ref(false)

</script>

<template>
    <!-- Section: Note -->
    <!-- <div v-if="notesData" class="h-fit lg:max-h-64 w-full flex lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notesData" :key="index+note.label" :noteData="note" />
    </div> -->

    <!-- Section: Stats box -->
    <div class="px-4 py-5 md:px-6 lg:px-8 flex gap-x-8 lg:gap-x-12 gap-y-3">

        <div class="space-y-3">
            <!-- Section: Radio -->
            <TabSelector :optionRadio="optionRadio" :radioValue="radioValue" :updateRoute="data.updateRoute"/>
            
            <div class="space-y-3">
                <!-- Section: Profile box -->
                <div v-if="props.data.fulfilment_customer.radioTabs.dropshipping" class="">
                    <h2 class="sr-only">Customer profile</h2>
                    <div class="rounded-lg shadow-sm ring-1 ring-gray-900/5">
                        <dl class="flex flex-wrap">
                            <!-- Profile: Header -->
                            <div class="flex w-full py-6">
                                <div class="flex-auto pl-6">
                                    <dt class="text-sm font-semibold leading-6 text-gray-900">Total Clients</dt>
                                    <dd class="mt-1 text-base font-semibold leading-6 text-gray-900">{{ data.customer.number_active_clients || 0 }}</dd>
                                </div>
                                <div class="flex-none self-end px-6 pt-4">
                                    <dt class="sr-only">Reference</dt>
                                    <dd class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                        {{ data.customer.reference }}
                                    </dd>
                                </div>
                            </div>
                            <!-- Section: Field -->
                            <div class="flex flex-col gap-y-3 border-t border-gray-900/5 w-full py-6">
                                <!-- Field: Contact name -->
                                <div v-if="data.customer.contact_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                                    <dt v-tooltip="'Contact name'" class="flex-none">
                                        <span class="sr-only">Contact name</span>
                                        <FontAwesomeIcon icon='fal fa-user' class='text-gray-400' fixed-width aria-hidden='true' />
                                    </dt>
                                    <dd class="text-gray-500">{{ data.customer.contact_name }}</dd>
                                </div>
                                <!-- Field: Contact name -->
                                <div v-if="data.customer.company_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                                    <dt v-tooltip="'Company name'" class="flex-none">
                                        <span class="sr-only">Company name</span>
                                        <FontAwesomeIcon icon='fal fa-building' class='text-gray-400' fixed-width aria-hidden='true' />
                                    </dt>
                                    <dd class="text-gray-500">{{ data.customer.company_name }}</dd>
                                </div>
                                <!-- Field: Created at -->
                                <div v-if="data.customer?.created_at" class="flex items-center w-full flex-none gap-x-4 px-6">
                                    <dt v-tooltip="'Created at'" class="flex-none">
                                        <span class="sr-only">Created at</span>
                                        <FontAwesomeIcon icon='fal fa-calendar-alt' class='text-gray-400' fixed-width aria-hidden='true' />
                                    </dt>
                                    <dd class="text-gray-500">
                                        <time datetime="2023-01-31">{{ useFormatTime(data.customer?.created_at) }}</time>
                                    </dd>
                                </div>
                                <!-- Field: Email -->
                                <div v-if="data.customer?.email" class="flex items-center w-full flex-none gap-x-4 px-6">
                                    <dt v-tooltip="'Email'" class="flex-none">
                                        <span class="sr-only">Email</span>
                                        <FontAwesomeIcon icon='fal fa-envelope' class='text-gray-400' fixed-width aria-hidden='true' />
                                    </dt>
                                    <dd class="text-gray-500">{{ data.customer?.email }}</dd>
                                </div>
                                <!-- Field: Phone -->
                                <div v-if="data.customer?.phone" class="flex items-center w-full flex-none gap-x-4 px-6">
                                    <dt v-tooltip="'Phone'" class="flex-none">
                                        <span class="sr-only">Phone</span>
                                        <FontAwesomeIcon icon='fal fa-phone' class='text-gray-400' fixed-width aria-hidden='true' />
                                    </dt>
                                    <dd class="text-gray-500">{{ data.customer?.phone }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>
                <!-- Box Group: Pallets -->
                <CustomerShowcaseStats :pieData="data.pieData"/>
            </div>
        </div>

        <!-- Section: Rental Agreement box -->
        <div class="w-full max-w-96">
            <div class="rounded-lg ring-1 ring-gray-200">
                <div class="bg-slate-100 border-b border-gray-300 py-4 px-4 flex justify-between">
                    <div class="font-semibold text-2xl">Rental Agreement</div>
                    <Link v-if="data.rental_agreement" :href="route(data.rental_agreement.data.route.name, data.rental_agreement.data.route.parameters)" @start="() => isLoadingEditAgreement = true" @cancel="() => isLoadingEditAgreement = false">
                        <Button type="edit" :loading="isLoadingEditAgreement"/>
                    </Link>
                </div>

                <!-- Stats -->
                <div v-if="data.rental_agreement" class="p-5 space-y-5">
                    <div class="flex flex-col">
                        <div class="text-sm text-gray-500">Reference:</div>
                        <div class="font-semibold text-xl text-gray-600">#{{ data.rental_agreement.data.reference }}</div>
                    </div>

                    <div class="flex flex-col">
                        <div class="text-sm text-gray-500">Billing Cycle:</div>
                        <div class="font-semibold text-xl text-gray-600 capitalize">{{ data.rental_agreement.data.billing_cycle }}</div>
                    </div>

                    <div class="flex flex-col">
                        <div class="text-sm text-gray-500">Pallet Limit:</div>
                        <div class="font-semibold text-xl text-gray-600">{{ data.rental_agreement.data.pallets_limit }}</div>
                    </div>
                </div>
                
                <div v-else class="text-center py-16 space-y-2">
                    <div class="text-gray-500">The rental is not created yet.</div>
                    <Button label="Create Rental Agreement" />
                </div>
            </div>
        </div>
    </div>
</template>


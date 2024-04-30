<script setup lang='ts'>
import { capitalize } from '@/Composables/capitalize'
import { Link } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faIdCardAlt, faMapMarkedAlt, faPhone } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import BoxStatsPalletDelivery from '@/Components/Pallet/BoxStatsPalletDelivery.vue'
import Table from '@/Components/Table/Table.vue'
import FulfilmentInvoiceCalculation from "@/Components/Fulfilment/FulfilmentInvoiceCalculation.vue"
import { Table as TableTS} from '@/types/Table'
import { Calculation, ProductTransaction } from '@/types/Invoices'
import { routeType } from '@/types/route'

library.add(faIdCardAlt, faMapMarkedAlt, faPhone)


const props = defineProps<{
    data: {
        calculation: Calculation 
        currency: string
        customer: {
            company_name: string
            contact_name: string
            location: string[]
            phone: string
            reference: string
            slug: string
        }
        items: {
            data: ProductTransaction[]
        }
        exportPdfRoute: routeType
        // items: TableTS
    }
}>()

// console.log('Invoice Showcase', props.data)

</script>

<template>
    <div class="h-min grid grid-cols-4 border-b border-gray-200 divide-x divide-gray-300">
        <!-- Section Calculation -->
        <div class="col-span-3 py-3">
            <FulfilmentInvoiceCalculation :pdfRoute="data.exportPdfRoute" :dataCalculations="data.calculation" :dataTable="data.items.data" />
        </div>

        <!-- Box: Customer -->
        <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" :tooltip="trans('Customer')" icon="fal fa-user">
            <!-- Field: Registration Number -->
            <Link as="a" v-if="data.customer.reference"
                :href="'#'"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer specialUnderlineSecondary">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Registration number</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">#{{ data.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="data.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ data.customer.contact_name }}</dd>
            </div>

            <!-- Field: Company name -->
            <div v-if="data.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ data.customer.company_name }}</dd>
            </div>

            <!-- Field: Tax number -->
            <!-- <div v-if="data.customer.tax_number"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Tax Number</span>
                    <FontAwesomeIcon icon='fal fa-passport' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ data.customer.tax_number }}</dd>
            </div> -->

            <!-- Field: Location -->
            <div v-if="data.customer.location"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Location</span>
                    <FontAwesomeIcon icon='fal fa-map-marked-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ data.customer.location.join(', ') }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="data.customer.phone"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ data.customer.phone }}</dd>
            </div>
        </BoxStatsPalletDelivery>

        <!-- TODO: Order link, Category, Date -->
        <!-- Box: Invoice Information -->
        <!-- <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" :tooltip="trans('Invoice informatino')"
            icon="fal fa-truck-couch">
            <div class="pt-1">
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <div>Items net</div>
                        <div>68.50</div>
                    </div>
                    <div class="flex justify-between">
                        <div>Charges</div>
                        <div>68.50</div>
                    </div>
                    <div class="flex justify-between">
                        <div>Shipping</div>
                        <div>68.50</div>
                    </div>
                    <div class="flex justify-between">
                        <div>Insurance</div>
                        <div>68.50</div>
                    </div>
                </div>

                <hr class="my-1.5 border-gray-300">

                <div class="space-y-1">
                    <div class="flex justify-between">
                        <div>Total net</div>
                        <div>68.50</div>
                    </div>
                    <div class="flex justify-between">
                        <div>Tax <span class="text-sm text-gray-400">(VAT 20%)</span></div>
                        <div>68.50</div>
                    </div>
                </div>

                <hr class="my-1.5 border-gray-300">

                <div class="flex justify-between font-bold">
                    <div class="">Total</div>
                    <div>68.50</div>
                </div>
            </div>
        </BoxStatsPalletDelivery> -->

        <!-- Box: Calculation -->
        <!-- <BoxStatsPalletDelivery class="col-span-2 py-5 px-3">
            <div class="px-4 max-w-xl">
                <div class="px-5 py-5 border-2 border-gray-300 rounded-md shadow text-gray-500">
                    <div class="mb-4 space-y-1">
                        <div class="text-2xl font-bold text-gray-600 leading-none">
                            Calculation
                            <span class="text-gray-400 text-sm font-light">(#{{data.calculation.number}})</span>
                        </div>
                        <div class="flex gap-x-2">
                            <div class="text-sm text-gray-500">
                                Profit: {{ locale.currencyFormat(data.currency, data.calculation.profit_amount || 0) }},
                            </div>
                            <div class="text-sm text-gray-500">
                                Margin: {{ data.calculation.margin_percentage || 0 }}%
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <div class="">Items Gross</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.item_gross || 0) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="">Discounts</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.discounts_total || 0) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="">Items net</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.items_net || 0) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="">Charges</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.charges || 0) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="">Shipping</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.shipping || 0) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="">Insurance</div>
                            <div class="text-gray-400">{{ locale.currencyFormat(data.currency, data.calculation.insurance || 0) }}</div>
                        </div>
                    </div>
                    
                    <hr class="my-2.5 border-gray-300">

                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <div>Total net</div>
                            <div>{{ locale.currencyFormat(data.currency, data.calculation.net_amount || 0)}}</div>
                        </div>
                        <div class="flex justify-between">
                            <div>Tax <span class="text-sm text-gray-400">(VAT {{ data.calculation.tax_percentage || 20 }}%)</span></div>
                            <div>{{ locale.currencyFormat(data.currency, data.calculation.tax_amount || 0)}}</div>
                        </div>
                        <div class="flex justify-between">
                            <div>Paid</div>
                            <div>{{ locale.currencyFormat(data.currency, data.calculation.payment_amount || 0)}}</div>
                        </div>
                    </div>

                    <hr class="my-2.5 border-gray-300">

                    <div class="flex justify-between font-semibold text-lg">
                        <div class="">Total</div>
                        <div>{{ locale.currencyFormat(data.currency, data.calculation.total_amount || 0)}}</div>
                    </div>
                </div>
            </div>
        </BoxStatsPalletDelivery> -->
    </div>

    <!-- <Table resources="" /> -->
</template>
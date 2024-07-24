<script setup lang='ts'>
import { Calculation, ProductTransaction } from '@/types/Invoices'
import { routeType } from '@/types/route'
import Table from '@/Components/Table/Table.vue'
import FulfilmentInvoiceCalculation from "@/Components/Fulfilment/FulfilmentInvoiceCalculation.vue"

const props = defineProps<{
    data: object
    tab: string
}>()


// console.log('Invoice Showcase', props.data)

</script>

<template>
    <!-- <pre>{{ usePage().props }}</pre> -->
    <div class="h-min   ">

        <!-- Section Calculation -->
        <div class="rounded-md">
            <Table :data="data" :name="tab">

            </Table>
            <!-- For Retina (don't delete) -->
            <!-- <FulfilmentInvoiceCalculation :pdfRoute="data.exportPdfRoute" :dataCalculations="data.calculation" :dataTable="data.items.data" /> -->
        </div>


        <!-- TODO: Order link, Category, Date -->
        <!-- Box: Invoice Information -->


        <!-- Box: Calculation -->
        <!-- <BoxStatPallet class="col-span-2 py-5 px-3">
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
        </BoxStatPallet> -->
    </div>

    <!-- <Table resources="" /> -->
</template>

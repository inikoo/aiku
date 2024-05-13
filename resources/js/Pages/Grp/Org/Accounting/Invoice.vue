<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 10:36:47 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import PageHeading from '@/Components/Headings/PageHeading.vue'
import { Link } from '@inertiajs/vue3'

import { computed, defineAsyncComponent, ref } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import ModelDetails from "@/Components/ModelDetails.vue"
import TablePayments from "@/Components/Tables/Grp/Accounting/TablePayments.vue"
import OperationsInvoiceShowcase from "@/Components/Showcases/Grp/Fulfilment/OperationsInvoiceShowcase.vue"
// import TableOperationsInvoiceItems from "@/Components/Tables/TableOperationsInvoiceItems.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { capitalize } from "@/Composables/capitalize"
import { trans } from 'laravel-vue-i18n'
import BoxStatsPalletDelivery from '@/Components/Pallet/BoxStatsPalletDelivery.vue'
import { Calculation, ProductTransaction } from '@/types/Invoices'
import { routeType } from '@/types/route'
import PDFSvg from '@/../art/app/pdf.svg'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faIdCardAlt, faMapMarkedAlt, faPhone, faChartLine, faCreditCard, faCube, faFolder, faPercent } from '@fal'
import { faClock, faFileInvoice } from '@fas'
library.add(faIdCardAlt, faMapMarkedAlt, faPhone, faFolder, faCube, faChartLine, faCreditCard, faClock, faFileInvoice, faPercent)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))


import { useLocaleStore } from '@/Stores/locale'
const locale = useLocaleStore()

const props = defineProps<{
    title: string,
    pageHead: {}
    tabs: {
        current: string
        navigation: {}
    }
    showcase: {
        invoice_information: Calculation 
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
    items: {}
    payments: {}
    details: {}
    history: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components = {
        showcase: OperationsInvoiceShowcase,
        // items: TableOperationsInvoiceItems,
        payments: TablePayments,
        details: ModelDetails,
        history: ModelChangelog,
    }

    return components[currentTab.value]
})


const boxInvoiceInformation = [
    [
        {
            name: "item_gross",
            label: "Item gross",
            value: props.showcase?.invoice_information.item_gross
        },
        {
            name: "discounts",
            label: "Discounts",
            value: props.showcase?.invoice_information.discounts_total
        },
        {
            name: "items_net",
            label: "Items net",
            value: props.showcase?.invoice_information.items_net
        },
        {
            name: "charges",
            label: "Charges",
            value: props.showcase?.invoice_information.charges
        },
        {
            name: "shipping",
            label: "Shipping",
            value: props.showcase?.invoice_information.shipping
        },
        {
            name: "insurance",
            label: "Insurance",
            value: props.showcase?.invoice_information.insurance
        },
    ],
    [
        {
            name: "total_net",
            label: "Total net",
            value: props.showcase?.invoice_information.net_amount
        },
        {
            name: "tax",
            label: "Tax",
            value: props.showcase?.invoice_information.tax_amount
        },
    ],
    [
        {
            name: "total",
            label: "Total",
            value: props.showcase?.invoice_information.total_amount
        },
    ]
]
</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other >
            <Link v-if="showcase.exportPdfRoute.name" :href="route(showcase.exportPdfRoute.name, showcase.exportPdfRoute.parameters)"
                class="active:ring-2 active:ring-gray-500"
            >
                <img src="@/../art/app/pdf.svg" class="h-7 rounded-md cursor-pointer" />
            </Link>
        
        </template>
    </PageHeading>
    
    
    <div class="grid grid-cols-4 divide-x divide-gray-300 border-b border-gray-200">
        <!-- Box: Customer -->
        <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" :tooltip="trans('Customer')" icon="fal fa-user">
            <!-- Field: Registration Number -->
            <Link as="a" v-if="showcase?.customer.reference"
                :href="'#'"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer specialUnderlineSecondary">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Registration number</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">#{{ showcase?.customer.reference }}</dd>
            </Link>
            <!-- Field: Contact name -->
            <div v-if="showcase?.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ showcase?.customer.contact_name }}</dd>
            </div>
            <!-- Field: Company name -->
            <div v-if="showcase?.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ showcase?.customer.company_name }}</dd>
            </div>
            <!-- Field: Tax number -->
            <!-- <div v-if="showcase?.customer.tax_number"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Tax Number</span>
                    <FontAwesomeIcon icon='fal fa-passport' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ showcase?.customer.tax_number }}</dd>
            </div> -->
            <!-- Field: Location -->
            <div v-if="showcase?.customer.location"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Location</span>
                    <FontAwesomeIcon icon='fal fa-map-marked-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ showcase?.customer.location.join(', ') }}</dd>
            </div>
            <!-- Field: Phone -->
            <div v-if="showcase?.customer.phone"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ showcase?.customer.phone }}</dd>
            </div>
        </BoxStatsPalletDelivery>

        <!-- Section: Invoice Information (looping) -->
        <BoxStatsPalletDelivery class="col-start-4 pb-2 py-5 px-3" :tooltip="trans('Invoice information')">
            <div class="pt-1 text-gray-500">
                <template v-for="invoiceGroup in boxInvoiceInformation">
                    <div class="space-y-1">
                        <div v-for="invoice in invoiceGroup" class="flex justify-between"
                            :class="invoice.label == 'Total' ? 'font-semibold' : ''"
                        >
                            <div>{{ invoice.label }} <span v-if="invoice.label == 'Tax'" class="text-sm text-gray-400">(VAT {{showcase?.invoice_information.tax_percentage || 0}}%)</span></div>
                            <div>{{ locale.currencyFormat(showcase?.currency, invoice.value || 0) }}</div>
                        </div>
                    </div>
                    <hr class="last:hidden my-1.5 border-gray-300">
                </template>
            </div>
        </BoxStatsPalletDelivery>
    </div>

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab" />
</template>

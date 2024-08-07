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
import type { Component } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import AddressSelector from "@/Components/DataDisplay/AddressSelector.vue"
import ModelDetails from "@/Components/ModelDetails.vue"
import TablePayments from "@/Components/Tables/Grp/Org/Accounting/TablePayments.vue"
import OperationsInvoiceShowcase from "@/Components/Tables/Grp/Org/Accounting/TableInvoiceTransactions.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { capitalize } from "@/Composables/capitalize"
import { trans } from 'laravel-vue-i18n'
import BoxStatPallet from '@/Components/Pallet/BoxStatPallet.vue'
import { Calculation, ProductTransaction } from '@/types/Invoices'
import { routeType } from '@/types/route'
import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import { FieldOrderSummary } from '@/types/Pallet'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faIdCardAlt, faMapMarkedAlt, faPhone, faChartLine, faCreditCard, faCube, faFolder, faPercent, faCalendarAlt, faDollarSign, faMapMarkerAlt, faPencil } from '@fal'
import { faClock, faFileInvoice, faFilePdf } from '@fas'
library.add(faIdCardAlt, faMapMarkedAlt, faPhone, faFolder, faCube, faChartLine, faCreditCard, faClock, faFileInvoice, faPercent, faCalendarAlt, faDollarSign, faFilePdf, faMapMarkerAlt, faPencil)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))


import { useLocaleStore } from '@/Stores/locale'
import { useFormatTime } from '@/Composables/useFormatTime'
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import TableInvoiceTransactions from "@/Components/Tables/Grp/Org/Accounting/TableInvoiceTransactions.vue";
import { Address } from '@/types/PureComponent/Address'
import { Icon } from '@/types/Utils/Icon'
// import AddressLocation from '@/Components/Elements/Info/AddressLocation.vue'
const locale = useLocaleStore()

interface InvoiceResource {
    address: Address
    created_at: string
    date: string
    net_amount: string
    number: string
    paid_at: string
    slug: string
    tax_liability_at: string
    total_amount: string
    type: {
        icon: Icon
        label: string
    }
    currency_code: string
    updated_at: string
}

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: {
        current: string
        navigation: {}
    }

    box_stats: {
        customer: {
            company_name: string
            contact_name: string
            location: string[]
            phone: string
            reference: string
            slug: string
        }

    }
    exportPdfRoute: routeType
    order_summary: FieldOrderSummary[][]
    recurring_bill_route: routeType
    invoice: InvoiceResource
    items: {}
    payments: {}
    details: {}
    history: {}
}>()


const currentTab = ref<string>(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        items: TableInvoiceTransactions,
        payments: TablePayments,
        details: ModelDetails,
        history: ModelChangelog,
    }

    return components[currentTab.value]
})

const boxFieldDetail = [
    {
        icon: 'fal fa-receipt',
        label: 'xxxdd',
        tooltip: 'Recurring bill'
    },
    {
        icon: 'fal fa-calendar-alt',
        label: useFormatTime(props.invoice.date),
        tooltip: 'Invoice created'
    },
    {
        icon: 'fal fa-dollar-sign',
        label: locale.currencyFormat(props.invoice.currency_code || 'usd', Number(props.invoice.total_amount)),
        tooltip: 'Amount need to pay by customer'
    },
]

console.log('pp', props.recurring_bill_route)
</script>


<template>
    <!-- <pre>{{ invoice }}</pre> -->

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">

        <!-- Button: PDF -->
        <template #other >
            <a v-if="exportPdfRoute?.name" :href="route(exportPdfRoute.name, exportPdfRoute.parameters)" target="_blank" class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none text-base" v-tooltip="trans('Download in')">
                <Button label="PDF" icon="fas fa-file-pdf" type="tertiary" />
            </a>
        </template>
    </PageHeading>
    
    <div class="grid grid-cols-4 divide-x divide-gray-300 border-b border-gray-200">
        <!-- Box: Customer -->
        <BoxStatPallet class=" py-2 px-3" icon="fal fa-user">

            <!-- Field: Registration Number -->
            <Link as="a" v-if="box_stats?.customer.reference"
                :href="'#'"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer primaryLink">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Registration number</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">#{{ box_stats?.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="box_stats?.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats?.customer.contact_name }}</dd>
            </div>

            <!-- Field: Company name -->
            <div v-if="box_stats?.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats?.customer.company_name }}</dd>
            </div>

            <!-- Field: Tax number -->
            <!-- <div v-if="box_stats?.customer.tax_number"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Tax Number</span>
                    <FontAwesomeIcon icon='fal fa-passport' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats?.customer.tax_number }}</dd>
            </div> -->

            <!-- Field: Location -->
            <!-- <div v-if="box_stats?.customer.location"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Location'" class="flex-none">
                    <span class="sr-only">Location</span>
                    <FontAwesomeIcon icon='fal fa-map-marked-alt' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">
                    <AddressLocation :data="box_stats?.customer.location" />
                </dd>
            </div> -->

            <!-- Field: Phone -->
            <div v-if="box_stats?.customer.phone"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats?.customer.phone }}</dd>
            </div>

            <!-- Field: Address -->
            <div class="flex items-start w-full gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                
                <dd class="text-xs text-gray-500 w-full">
                    <div v-if="invoice.address" class="relative bg-gray-50 border border-gray-300 rounded px-2 py-1">
                        <div v-html="invoice.address.formatted_address" />
                    </div>

                    <div v-else class="text-gray-400 italic">
                        No address
                    </div>
                </dd>
            </div>
        </BoxStatPallet>

        <!-- Section: Detail -->
        <BoxStatPallet class="py-2 px-3">
            <div class="mt-1">
                <div v-for="field in boxFieldDetail" v-tooltip="field.tooltip" class="flex items-center w-full flex-none gap-x-2">
                    <dt class="flex-none">
                        <FontAwesomeIcon
                            :icon='field.icon'
                            fixed-width aria-hidden='true'
                            class="text-gray-500"
                        />
                    </dt>
                    <dd class="text-xs text-gray-500" :class='"ff"'>
                        {{ field.label }}
                    </dd>
                </div>
            </div>
        </BoxStatPallet>

        <!-- Section: Order Summary -->
        <BoxStatPallet class="col-start-3 col-span-2 py-2 px-3">
            <OrderSummary :order_summary :currency_code="invoice.currency_code" />
        </BoxStatPallet>

        <!-- Section: Invoice Information (looping) -->
        <!-- <BoxStatPallet class="col-start-4 py-2 px-3"')">
            <div class="pt-1 text-gray-500">
                <template v-for="invoiceGroup in boxInvoiceInformation">
                    <div class="space-y-1">
                        <div v-for="invoice in invoiceGroup" class="flex justify-between"
                            :class="invoice.label == 'Total' ? 'font-semibold' : ''"
                        >
                            <div>{{ invoice.label }} <span v-if="invoice.label == 'Tax'" class="text-sm text-gray-400">(VAT {{invoice.tax_percentage || 0}}%)</span></div>
                            <div>{{ locale.currencyFormat(box_stats?.currency, invoice.value || 0) }}</div>
                        </div>
                    </div>
                    <hr class="last:hidden my-1.5 border-gray-300">
                </template>
            </div>
        </BoxStatPallet> -->
    </div>

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab" />
</template>
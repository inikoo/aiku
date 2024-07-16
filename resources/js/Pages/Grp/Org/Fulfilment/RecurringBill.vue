<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, defineAsyncComponent, ref } from 'vue'
import type { Component } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'


import StartEndDate from '@/Components/Utils/StartEndDate.vue'
import RecurringBillShowcase from '@/Pages/Grp/Org/Fulfilment/RecurringBillShowcase.vue'
import BoxStatsRecurringBills from '@/Components/Fulfilment/BoxStatsRecurringBills.vue'

import TablePallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePallets.vue'
import type { Timeline } from '@/types/Timeline'


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    showcase: {}
    pallets: {}

    timeline_rb: Timeline[]
    box_stats: {}


}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        showcase: RecurringBillShowcase,
        pallets: TablePallets
    }

    return components[currentTab.value]
})

const fakeBoxStats = {
    "fulfilment_customer": {
        "radioTabs": {
            "pallets_storage": true,
            "items_storage": false,
            "dropshipping": false
        },
        "number_pallets": 34,
        "number_pallets_state_received": 0,
        "number_stored_items": 0,
        "number_pallet_deliveries": 1,
        "number_pallet_returns": 0,
        "slug": "airhead-designs",
        "fulfilment": {
            "slug": "awf",
            "name": "AW Fulfilment"
        },
        "customer": {
            "slug": "airhead-designs-ltd",
            "reference": "415850",
            "name": "airHEAD Designs Ltd",
            "contact_name": "Holly Galbraith",
            "company_name": "airHEAD Designs Ltd",
            "location": [
                "GB",
                "United Kingdom",
                "London"
            ],
            "email": "accounts@ventete.com",
            "phone": "+447725269253",
            "created_at": "2021-12-01T09:46:06.000000Z"
        }
    },
    "delivery_status": {
        "tooltip": "Booked in",
        "icon": "fal fa-check-double",
        "class": "text-purple-500",
        "color": "purple",
        "app": {
            "name": "check-double",
            "type": "font-awesome-5"
        }
    },
    "order_summary": [
        [
            {
                "label": "Pallets",
                "quantity": 13,
                "price_base": 999,
                "price_total": 1111
            },
            {
                "label": "Services",
                "quantity": 2,
                "price_base": "Multiple",
                "price_total": 0
            },
            {
                "label": "Physical Goods",
                "quantity": 1,
                "price_base": "Multiple",
                "price_total": 0
            }
        ],
        [
            {
                "label": "Shipping",
                "information": "Shipping fee to your address using DHL service.",
                "price_total": 1111
            },
            {
                "label": "Tax",
                "information": "Tax is based on 10% of total order.",
                "price_total": 1111
            }
        ],
        [
            {
                "label": "Total",
                "price_total": null
            }
        ]
    ]
}

const fakePalletDelivery = {
    "id": 1,
    "customer_name": "airHEAD Designs Ltd",
    "reference": "AD-001",
    "state": "booked-in",
    "timeline": {
        "in-process": {
            "label": "In Process",
            "tooltip": "In Process",
            "key": "in-process",
            "timestamp": "2024-07-14T21:50:56.000000Z"
        },
        "submitted": {
            "label": "Submitted",
            "tooltip": "Submitted",
            "key": "submitted",
            "timestamp": "2024-07-14T21:53:30.000000Z"
        },
        "confirmed": {
            "label": "Confirmed",
            "tooltip": "Confirmed",
            "key": "confirmed",
            "timestamp": "2024-07-14T21:55:14.000000Z"
        },
        "received": {
            "label": "Received",
            "tooltip": "Received",
            "key": "received",
            "timestamp": "2024-07-14T21:55:43.000000Z"
        },
        "booking-in": {
            "label": "Booking In",
            "tooltip": "Booking In",
            "key": "booking-in",
            "timestamp": "2024-07-14T21:57:11.000000Z"
        },
        "booked-in": {
            "label": "Booked In",
            "tooltip": "Booked In",
            "key": "booked-in",
            "timestamp": "2024-07-14T22:30:52.000000Z"
        }
    },
    "number_pallets": 13,
    "number_services": 2,
    "number_physical_goods": 1,
    "state_label": "Booked In",
    "state_icon": {
        "tooltip": "Booked in",
        "icon": "fal fa-check-double",
        "class": "text-purple-500",
        "color": "purple",
        "app": {
            "name": "check-double",
            "type": "font-awesome-5"
        }
    },
    "estimated_delivery_date": null
}

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <!-- Section: Timeline -->
    <!-- <div class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline :options="timeline_rb" :slidesPerView="6" />
    </div> -->

    <div class="grid grid-cols-2">
        <div class="py-4 px-3">
            <div class="flex flex-col justify-center h-full w-full rounded-md px-4 py-2 bg-green-100 ring-1 ring-green-500 text-green-700">
                <div class="text-xs">Status</div>
                <div class="font-semibold">Current</div>
            </div>
        </div>
        <div class="py-1">
            <StartEndDate />
        </div>
    </div>

    <BoxStatsRecurringBills :dataPalletDelivery="fakePalletDelivery" :boxStats="fakeBoxStats" />

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>
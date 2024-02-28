<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { useLayoutStore } from "@/Stores/layout"
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'
import { Line } from 'vue-chartjs'
import PureRadio from '@/Components/Pure/PureRadio.vue'
import { ref } from 'vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import CustomerShowcaseStats from '@/Components/Showcases/Grp/CustomerShowcaseStats.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle } from '@fas'
import { faCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { router } from "@inertiajs/vue3"
import { routeType } from '@/types/route'
library.add(faCheckCircle, faCircle)

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps<{
    data: {
        customer: {
            slug: string
            reference: string
            name: string
            contact_name: string
            company_name?: string
            email?: string
            phone?: string
            created_at: Date
            updated_at: Date
            shop?: string
            shop_slug?: string
            number_active_clients?: number
        }
        fulfilment_customer: {
            number_pallets?: number
            number_pallets_state_received?: number
            number_stored_items?: number
            number_pallets_deliveries?: number
            number_pallets_returns?: number
        }
        updateRoute: routeType
    },
    tab: string
}>()

const radioValue = ref<string[]>(['pallets_storage'])
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

// Radio: Interest
const onClickRadio = (value: string) => {
    console.log('qqq', route(props.data.updateRoute.name, props.data.updateRoute.parameters))
    // If value already selected
    if (radioValue.value.includes(value)) {
        router.patch(route(props.data.updateRoute.name, props.data.updateRoute.parameters),
            {
                data: radioValue.value
            }, {
                onSuccess: (e) => console.log('on Success', e),
                // onFinish: () => console.log('on Finish')
            })
        // If value is more than 1 then delete
        if (radioValue.value.length > 1) {
            const index = radioValue.value.indexOf(value)
            radioValue.value.splice(index, 1)
        }
    } else {
        router.patch(route(props.data.updateRoute.name, props.data.updateRoute.parameters),
            {
                data: radioValue.value
            }, {
                onSuccess: (e) => console.log('on Success', e),
                onFinish: () => console.log('on Finish')
        })
        radioValue.value.push(value)
    }
}

</script>

<template>

    <!-- Section: Radio -->
    <div class="px-8 mt-4 flex gap-x-2">
        <div v-for="radio in optionRadio"
            @click="(e) => onClickRadio(radio.value)"
            class="rounded-lg w-fit px-3 py-2 select-none cursor-pointer border"    
        >
            <FontAwesomeIcon v-if="radioValue.includes(radio.value)" icon='fas fa-check-circle' class='text-lime-500' fixed-width aria-hidden='true' />
            <FontAwesomeIcon v-else icon='fal fa-circle' class='text-lime-600' fixed-width aria-hidden='true' />
            {{ radio.label }}
        </div>
    </div>

    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-3">
        <!-- Section: Profile box -->
        <div class="text-slate-700 p-6 flex flex-col justify-between rounded-md border border-gray-200 shadow overflow-hidden">
            <div class="w-full">
                <h2 class="text-3xl font-bold">{{ data.customer.name }}</h2>
                <div class="text-lg">
                    {{ data.customer.shop }}<span class="text-gray-400">({{ data.customer.number_active_clients || 0 }}
                        clients)</span>
                </div>
            </div>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-bold">Phone</h3>
                    <address class="not-italic text-slate-300">
                        <p>{{ data.customer.phone || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-bold">Email</h3>
                    <address class="not-italic text-slate-300">
                        <p>{{ data.customer.email || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-bold">Member since</h3>
                    <address class="not-italic text-slate-500">
                        <p>{{ useFormatTime(data.customer.created_at) || '-' }}</p>
                    </address>
                </div>
            </div>
        </div>

        <!-- Section: Stats box -->
        <CustomerShowcaseStats />
    </div>
</template>


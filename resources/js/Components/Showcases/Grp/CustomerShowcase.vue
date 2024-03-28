<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
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
import { faSync } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
library.add(faLink, faSync)

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

// Section: Webhook
const isWebhookLoading = ref(false)
const webhookValue = ref(props.data.customer.webhook_access_key || '')
const onRetrieveWebhook = () => {
    isWebhookLoading.value = true
    try {
        const response = axios.post('route')
        webhookValue.value = 'xxx'
    } catch (error) {
        notify({
            title: "Something wrong.",
            text: "Failed to retrieve webhook. Please try again.",
            type: "error"
        })
    }

    isWebhookLoading.value = false
}

</script>

<template>
    <!-- Section: Stats box -->
    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-4 gap-y-3">
        <!-- Section: Radio -->
        <TabSelector :optionRadio="optionRadio" :radioValue="radioValue" :updateRoute="data.updateRoute"/>
        
        <!-- Section: Webhook -->
        <div class="flex justify-center flex-col">
            <div class="whitespace-nowrap text-gray-500">The webhook: </div>
            <div v-if="webhookValue" class="bg-white border border-gray-300 flex items-center justify-between mx-auto rounded-md md:w-full md:max-w-2xl ">
                <a href="#" target="_blank" class="pl-4 md:pl-5 inline-block py-2 text-xxs md:text-base text-gray-400 w-full" v-tooltip="'Click to visit link'">{{ webhookValue }}</a>
                
                <div @click="() => onRetrieveWebhook()" class="cursor-pointer h-full aspect-square flex justify-center items-center">
                    <FontAwesomeIcon icon='fal fa-sync' class='text-gray-500' :class="isWebhookLoading ? 'animate-spin' : ''" aria-hidden='true' />
                </div>
                
                <Button :style="'tertiary'" class="" size="l" @click="useCopyText('dsadsa')" title="Copy url to clipboard">
                    <FontAwesomeIcon icon='far fa-link' class='text-gray-500' aria-hidden='true' />
                </Button>
            </div>
            <Button v-else label="Click to retrieve webhook" :loading="isWebhookLoading" @click="() => onRetrieveWebhook()" />
        </div>
        
        <!-- Section: Profile box -->
        <div class="border border-slate-200 text-retina-600 p-6 flex flex-col justify-between rounded-lg shadow overflow-hidden">
            <div class="w-full">
                <h2 v-if="data.customer?.name" class="text-3xl font-bold text-slate-600">{{ data.customer?.name }}</h2>
                <h2 v-else class="text-3xl font-light italic brightness-75">{{ trans('No name') }}</h2>
                <div class="text-lg">
                    {{ data.customer?.shop }}
                    <span class="text-gray-400">
                        ({{ data.customer?.number_active_clients || 0 }} clients)
                    </span>
                </div>
            </div>
            <div class="space-y-3 text-sm text-slate-500">
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Phone</h3>
                    <address class="text-base font-bold not-italic text-slate-600">
                        <p>{{ data.customer?.phone || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Email</h3>
                    <address class="text-base font-bold not-italic text-slate-600">
                        <p>{{ data.customer?.email || '-' }}</p>
                    </address>
                </div>
                <div class="border-l-2 border-slate-500 pl-4">
                    <h3 class="font-light">Member since</h3>
                    <address class="text-base font-bold not-italic text-slate-600">
                        <p>{{ useFormatTime(data.customer?.created_at) || '-' }}</p>
                    </address>
                </div>
            </div>
        </div>

        <!-- Box Group: Pallets -->
        <CustomerShowcaseStats :pieData="data.pieData"/>
    </div>
</template>


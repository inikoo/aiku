<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { inject } from 'vue'
import { FieldOrderSummary } from '@/types/Pallet'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faQuestionCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faQuestionCircle)

const props = defineProps<{
    currency_code?: string
    order_summary: FieldOrderSummary[][]
}>()

const locale = inject('locale', aikuLocaleStructure)
    
</script>

<template>
    <dl class="flex flex-col space-y-2 text-gray-500 rounded-lg text-sm">
        <template v-for="(summaryGroup, summaryRowIndex) in order_summary" :key="'fieldSummary' + summaryRowIndex">
            <div v-if="summaryGroup.length" class="pt-2 first:pt-0 pr-2 flex flex-col gap-y-2 first:border-t-0 border-t border-gray-200 ">
                <div v-for="fieldSummary in summaryGroup" class="grid grid-cols-7 gap-x-4 items-center justify-between">
                    <dt class="col-span-2 flex items-center">
                        <span>{{ fieldSummary.label }}</span>
                        <FontAwesomeIcon v-if="fieldSummary.information" icon='fal fa-question-circle' v-tooltip="fieldSummary.information" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                    </dt>
                    <Transition name="spin-to-down">
                        <dd :key="fieldSummary.quantity" class="justify-self-end text-sm">{{ typeof fieldSummary.quantity === 'number' ? locale.number(fieldSummary.quantity) : null}}</dd>
                    </Transition>
                    <!-- <dd class="col-span-2 place-self-end text-sm">{{ fieldSummary.price_base }}</dd> -->
                    <div class="relative col-span-4 place-self-end text-sm font-medium overflow-hidden">
                        <Transition name="spin-to-right">
                            <dd :key="fieldSummary.price_total" class="" :class="fieldSummary.price_total === 'free' ? 'text-green-600 animate-pulse' : ''">{{ locale.currencyFormat(order_summary?.currency?.data?.code || 'usd', fieldSummary.price_total || 0) }}</dd>
                        </Transition>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- <pre>{{ order_summary }}</pre> -->
    </dl>
</template>
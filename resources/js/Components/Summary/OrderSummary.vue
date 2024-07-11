<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { inject } from 'vue'
import { FieldOrderSummary } from '@/types/Pallet'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faQuestionCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faQuestionCircle)

const props = defineProps<{
    order_summary: FieldOrderSummary[][]
}>()

const locale = inject('locale', {})
    
</script>

<template>
    <dl class="mt-2 space-y-2 text-gray-500 border border-gray-300 px-4 py-3 rounded-lg bg-gray-50">
        <div class="grid grid-cols-7 gap-x-4 items-center justify-between font-bold text-sm">
            <dt class="col-span-2 flex items-center">{{ trans('Label') }}</dt>
            <dd class="place-self-end">{{ trans('Quantity') }}</dd>
            <dd class="col-span-2 place-self-end">{{ trans('Base price') }}</dd>
            <dd class="col-span-2 place-self-end">{{ trans('Total price') }}</dd>
        </div>

        <div v-for="(summaryGroup, summaryRowIndex) in order_summary" class="pr-2 flex flex-col gap-y-2 last:font-bold border-t border-gray-200 pt-2">
            <div v-for="fieldSummary in summaryGroup" class="grid grid-cols-7 gap-x-4 items-center justify-between">
                <dt class="col-span-2 flex items-center">
                    <span>{{ fieldSummary.label }}</span>
                    <FontAwesomeIcon v-if="fieldSummary.information" icon='fal fa-question-circle' v-tooltip="fieldSummary.information" class='ml-1 cursor-pointer text-gray-400 hover:text-gray-500' fixed-width aria-hidden='true' />
                </dt>
                <dd class="place-self-end text-sm">{{ fieldSummary.quantity ? locale.number(fieldSummary.quantity) : null}}</dd>
                <dd class="col-span-2 place-self-end text-sm">{{ fieldSummary.price_base }}</dd>
                <dd class="col-span-2 place-self-end text-sm font-medium" :class="fieldSummary.price_total === 'free' ? 'text-green-600 animate-pulse' : ''">{{ fieldSummary.price_total || 0 }}</dd>
            </div>
        </div>
        
        <!-- <pre>{{ order_summary }}</pre> -->
    </dl>
</template>
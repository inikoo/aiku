<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationTriangle)

const props = defineProps<{
    alert: {
        status: string
        title?: string
        description?: string
    }
}>()
</script>

<template>
    <div class="flex gap-x-3 p-4 rounded"
        :class="{
            'bg-amber-200 text-amber-600': alert.status === 'warning',
            'bg-red-200 text-red-600': alert.status === 'danger',
        }"
        :style="{
            'border': alert.status === 'warning' ? '2px solid #fbbf24' : alert.status === 'danger' ? '2px solid #f87171' : ''
        }"    
    >
        <div class="">
            <FontAwesomeIcon icon='fas fa-exclamation-triangle' size="lg" class='' fixed-width aria-hidden='true' />
        </div>

        <div class="">
            <h3 class="text-base font-semibold">{{ alert.title || trans('Attention needed') }}</h3>
            <div class="text-sm opacity-80 ">
                {{ alert.description }}
            </div>
        </div>
    </div>
</template>
<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'
import { inject } from 'vue'

const layout = inject('layout', {})

const props = defineProps<{
    tooltip?: string
    label?: string | number
    icon?: string | string[]
    percentage?: number
    color?: {
        bgColor?: string
        textColor?: string
    }
}>()
</script>

<template>
    <div class="relative flex flex-col justify-start" >
        <div v-if="tooltip" class="absolute top-0 left-0 text-xs border-b border-r border-gray-300 rounded-br py-0.5 pl-3 pr-4 shadow-sm"
            :style="{
                backgroundColor: color?.bgColor || '#fff',
                color: color?.textColor || layout?.app?.theme[0]
            }"
        >
            {{ trans(tooltip) }}
        </div>
        
        <!-- Section: Percentage (%) -->
        <div v-if="percentage" class="absolute top-0.5 right-0.5 tabular-nums text-xxs rounded-br-sm px-1">
            {{ percentage }}%
        </div>
        
        <!-- Section: Progress bar -->
        <div v-if="percentage" class="absolute -top-0.5 left-0 h-0.5 bg-green-500 w-full text-xxs rounded-br-sm px-1 transition-all"
            :style="{width: percentage + '%'}"
        />

        <div class="flex flex-col justify-center w-full flex-none gap-x-4 py-1">
            <slot>
                <div class="flex gap-x-3">
                    <dt class="flex-none">
                        <span class="sr-only">Contact name</span>
                        <FontAwesomeIcon v-if="icon" :icon='icon' class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500">{{ label }}</dd>
                </div>
            </slot>
        </div>
    </div>
</template>
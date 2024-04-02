<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'
import { inject } from 'vue'

const layout = inject('layout', {})

const props = defineProps<{
    tooltip: string
    label?: string | number
    icon?: string | string[]
    percentage?: number
}>()
</script>

<template>
    <div class="ring-1 ring-gray-300 rounded-md relative flex flex-col justify-start pb-2 py-5 px-3" >
        <div class="absolute top-0 left-0 text-xxs rounded-br py-0.5 px-1"
            :style="{
                backgroundColor: layout?.app?.theme[2],
                color: layout?.app?.theme[3]
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
                        <FontAwesomeIcon :icon='icon' class='text-gray-400' fixed-width aria-hidden='true' />
                    </dt>
                    <dd class="text-gray-500">{{ label }}</dd>
                </div>
            </slot>
        </div>
    </div>
</template>
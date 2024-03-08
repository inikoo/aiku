<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    layout: any
    tooltip: string
    label?: string | number
    icon: string | string[]
}>()
</script>

<template>
    <div v-tooltip="trans(tooltip)"
        class="relative flex flex-col justify-end pb-2 p-4 rounded-md bg-slate-50 border border-slate-300 overflow-hidden"
    >
        <div class="absolute top-0 left-0 text-xxs rounded-br-sm px-1"
            :style="{
                backgroundColor: layout?.app?.theme[2],
                color: layout?.app?.theme[3]
            }"
        >
            {{ trans(tooltip) }}
        </div>
        <div class="text-2xl font-bold">{{ label || '-' }}</div>
        <FontAwesomeIcon :icon="icon" class='text-zinc-800/30 absolute text-[40px] right-2' fixed-width aria-hidden='true' />
    </div>
</template>
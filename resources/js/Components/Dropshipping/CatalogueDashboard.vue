<script setup lang='ts'>

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faDollarSign } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import CountUp from 'vue-countup-v3'
library.add(faDollarSign)

const props = defineProps<{
    data: {
        label: string
        icon: string
        value: number
        meta: {
            value: number
            label: string
        }
    }[]
}>()

const locale = inject('locale', aikuLocaleStructure)
</script>

<template>
    <div class="flex gap-x-3 gap-y-4 p-4 flex-wrap">
        <div v-for="fake in data" class="bg-gray-50 min-w-64 border border-gray-300 rounded-md p-6">
            <div class="flex justify-between items-center mb-1">
                <div class="">{{ fake.label }}</div>
                <FontAwesomeIcon :icon='fake.icon' class=' text-xl text-gray-400' fixed-width aria-hidden='true' />
            </div>

            <div class="mb-1 text-2xl font-semibold">
                <CountUp
                    :endVal="fake.value"
                    :duration="1.5"
                    :scrollSpyOnce="true"
                    :options="{
                        formattingFn: (value: number) => locale.number(value)
                    }"
                />
            </div>
            <!-- <div class="text-sm text-gray-400">{{ fake.meta.value }} {{ fake.meta.label }}</div> -->
        </div>
    </div>
</template>
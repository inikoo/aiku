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
        description: string
        stats: {
            label: string
            icon: string
            value: number
            meta: {
                value: number
                label: string
            }
        }[]
    }
}>()

const locale = inject('locale', aikuLocaleStructure)
</script>

<template>
    <div class=" p-4">
        <div class="border-l-4 border-l-indigo-500 border border-gray-300 max-w-lg px-2 py-2.5 mb-10">
            <div class="text-sm text-gray-400 block">Description</div>
            {{ data.description }}
        </div>
        
        <div class="flex gap-x-3 gap-y-4 flex-wrap">
            <div v-for="fake in data.stats" class="bg-gray-50 min-w-64 border border-gray-300 rounded-md p-6">
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
    </div>
</template>
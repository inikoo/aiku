<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowUp, faArrowDown, faHandSparkles, faEnvelope } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faArrowUp, faArrowDown, faHandSparkles, faEnvelope)

const props = defineProps<{
    data: {}
}>()


const stats = [
    { id: 1, name: 'Total Subscribers', stat: '71,897', icon: 'fal fa-user', change: '122', changeType: 'increase' },
    { id: 2, name: 'Avg. Open Rate', stat: '58.16%', icon: 'fal fa-envelope', change: '5.4%', changeType: 'increase' },
    { id: 3, name: 'Avg. Click Rate', stat: '24.57%', icon: 'fal fa-hand-sparkles', change: '3.2%', changeType: 'decrease' },
]

</script>

<template>
    <div class="px-5 py-4">
        <h3 class="text-base font-semibold leading-6 text-gray-900">Last 30 days</h3>

        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="item in stats" :key="item.id"
                class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-indigo-500 p-3">
                        <FontAwesomeIcon :icon='item.icon' class='h-5 aspect-square text-white' fixed-width aria-hidden='true' />
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ item.name }}</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-gray-900">{{ item.stat }}</p>
                    <p
                        :class="[item.changeType === 'increase' ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                        <FontAwesomeIcon v-if="item.changeType === 'increase'" icon='fal fa-arrow-up' class='h-5 w-5 flex-shrink-0 self-center text-green-500' fixed-width aria-hidden='true' />
                        <FontAwesomeIcon v-else icon='fal fa-arrow-down' class='h-5 w-5 flex-shrink-0 self-center text-red-500' fixed-width aria-hidden='true' />
                        <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by </span>
                        {{ item.change }}
                    </p>
                    <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">View all<span
                                    class="sr-only"> {{ item.name }} stats</span></a>
                        </div>
                    </div>
                </dd>
            </div>
        </dl>
    </div>
</template>

<style scoped></style>

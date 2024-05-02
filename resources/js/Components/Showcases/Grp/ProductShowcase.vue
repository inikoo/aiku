<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
// import { usePage } from '@inertiajs/vue3'
import { useLocaleStore } from '@/Stores/locale'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCircle)

const props = defineProps<{
    data: {}
}>()

const locale = useLocaleStore()

const stats = [
    { name: '2024', stat: '71,897', previousStat: '70,946', change: '12%', changeType: 'increase' },
    { name: '2023', stat: '58.16%', previousStat: '56.14%', change: '2.02%', changeType: 'increase' },
    { name: '2022', stat: '24.57%', previousStat: '28.62%', change: '4.05%', changeType: 'decrease' },
    { name: '2021', stat: '71,897', previousStat: '70,946', change: '12%', changeType: 'increase' },
    { name: '2020', stat: '58.16%', previousStat: '56.14%', change: '2.02%', changeType: 'increase' },
    { name: '2019', stat: '24.57%', previousStat: '28.62%', change: '4.05%', changeType: 'decrease' },
]

</script>


<template>
    <div class="grid grid-cols-4 gap-x-1 gap-y-4">
        <div class="p-5 space-y-5">
            <div class="relative">
                <div class="bg-gray-100 h-full aspect-square rounded-lg shadow">
                
                </div>

                <div class="absolute bottom-0 left-0 py-4 px-4 w-full text-green-500 ">
                    <FontAwesomeIcon icon='fas fa-circle' class='animate-pulse' size="xs" fixed-width aria-hidden='true' />
                    The product is active
                </div>
            </div>

            <!-- Order summary -->
            <section aria-labelledby="summary-heading" class="border border-gray-200 rounded-lg px-4 py-6 sm:p-4 lg:mt-0 lg:p-5">
                <h2 id="summary-heading" class="text-lg font-medium">Product summary</h2>

                <dl class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Added date</dt>
                        <dd class="text-sm font-medium">24 Nov 2019</dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Stock</dt>
                        <dd class="text-sm font-medium">24 pcs</dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Cost</dt>
                        <dd class="text-sm font-medium">{{ locale.currencyFormat('usd', 8.80)}}</dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Price</dt>
                        <dd class="text-sm font-medium text-right">{{ locale.currencyFormat('usd', 13.50)}} <span class="font-light">margin (45.0%)</span></dd>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">RRP</dt>
                        <dd class="text-sm font-medium text-right">{{ locale.currencyFormat('usd', 2.95)}} <span class="font-light">margin (66.1%)</span></dd>
                    </div>

                    <!-- <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <dt class="flex items-center text-sm text-gray-600">
                            <span>Shipping estimate</span>
                            <a href="#" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Learn more about how shipping is calculated</span>
                                <QuestionMarkCircleIcon class="h-5 w-5" aria-hidden="true" />
                            </a>
                        </dt>
                        <dd class="text-sm font-medium">$5.00</dd>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <dt class="flex text-sm text-gray-600">
                            <span>Tax estimate</span>
                            <a href="#" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Learn more about how tax is calculated</span>
                            <QuestionMarkCircleIcon class="h-5 w-5" aria-hidden="true" />
                            </a>
                        </dt>
                        <dd class="text-sm font-medium">$8.32</dd>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <dt class="text-base font-medium">Order total</dt>
                        <dd class="text-base font-medium">$112.32</dd>
                    </div> -->
                </dl>

                <!-- <div class="mt-6">
                    <button type="submit" class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
            </section>
        </div>

        <!-- Revenue -->
        <div class="pt-8 p-4 col-span-3">
            <h3 class="text-base font-semibold leading-6">All sales since: Mon 20 August 2007</h3>
            <dl class="mt-5 grid grid-cols-1 overflow-hidden rounded bg-white md:grid-cols-3 md:gap-x-2 md:gap-y-4">
                <div v-for="item in stats" :key="item.name" class="px-4 py-5 sm:p-6 border border-gray-200 rounded-md">
                    <dt class="text-base font-normal">{{ item.name }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                            {{ item.stat }}
                            <span class="ml-2 text-sm font-medium text-gray-500">from {{ item.previousStat }}</span>
                        </div>
                        <div
                            :class="[item.changeType === 'increase' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium md:mt-2 lg:mt-0']">
                            <ArrowUpIcon v-if="item.changeType === 'increase'"
                                class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-green-500" aria-hidden="true" />
                            <ArrowDownIcon v-else class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-red-500"
                                aria-hidden="true" />
                            <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by
                            </span>
                            {{ item.change }}
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

</template>

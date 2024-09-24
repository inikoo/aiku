<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 13 Oct 2022 15:35:22 Central European Summer Plane Malaga - East Midlands UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { library } from "@fortawesome/fontawesome-svg-core"
import {  } from "@fal"
import { faCheckCircle, faTimesCircle } from "@fas"

import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as PageHeadingTS } from '@/types/PageHeading'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { inject } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import CountUp from 'vue-countup-v3'
import BackgroundBox from '@/Components/BackgroundBox.vue'

library.add(faCheckCircle, faTimesCircle )

const props = defineProps<{
    pageHead: PageHeadingTS
    tabs: {
        current: string
        navigation: {}
    },
    title: string
    stats?: {}
}>()


const locale = inject('locale', aikuLocaleStructure)

// const stats = [
//     { id: 1, label: 'Total Subscribers', stat: '71,897', change: '122', changeType: 'increase' },
//     { id: 2, label: 'Avg. Open Rate', stat: '58.16%', change: '5.4%', changeType: 'increase' },
//     { id: 3, label: 'Avg. Click Rate', stat: '24.57%', change: '3.2%', changeType: 'decrease' },
// ]


</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="p-6">
        <div>
            <div class="text-xl font-semibold py-1 border-b border-gray-200">Stats</div>
            <dl class="mt-4 grid grid-cols-1 gap-2 lg:gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="(stat, index) in stats"
                    :key="'stat' + index"
                    :href="route(stat.route.name, stat.route.parameters)"
                    :style="{color: stat.color}"
                    class="isolate relative overflow-hidden rounded-lg bg-white hover:bg-gray-100 cursor-pointer border border-gray-200 px-4 py-5 shadow-sm sm:p-6 sm:pb-3"
                >
                    <BackgroundBox class="-z-10 opacity-60 absolute top-0 right-0" />

                    <dt class="truncate text-sm font-medium text-gray-400">{{ stat.label }}</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight flex gap-x-2 items-center">
                        <FontAwesomeIcon :icon='stat.icon' class='text-xl' fixed-width aria-hidden='true' />
                        <CountUp
                            :endVal='stat.value'
                            :duration='1.5'
                            :scrollSpyOnce='true'
                            :options='{
                                formattingFn: (value: number) => locale.number(value)
                            }'
                        />
                    </dd>

                    <div v-if="stat.metas?.length" class="-ml-2 py-2 text-sm text-gray-500 flex gap-x-3 gap-y-0.5 items-center flex-wrap">
                        <Link
                            v-for="meta in stat.metas"
                            :is="meta.href?.name ? Link : 'div'"
                            :href="meta.href?.name ? route(meta.href.name, meta.href.parameters) : ''"
                            class="group/sub px-2 flex gap-x-0.5 items-center font-normal"
                            v-tooltip="capitalize(meta.icon?.tooltip)"
                        >
                            <FontAwesomeIcon :icon="meta.icon?.icon" class="md:opacity-50 group-hover/sub:opacity-100" :class="meta.icon?.class" fixed-width :title="meta.icon?.tooltip" aria-hidden="true" />
                            <div class="group-hover/sub:text-red-700">
                                {{ locale.number(meta.count) }} {{  }}
                            </div>
                        </Link>
                    </div>
                </Link>
            </dl>
        </div>
    </div>


    <div v-if="false" class="p-6">
        <h3 class="text-base font-semibold leading-6">Last 30 days</h3>

        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="item in stats" :key="item.id"
                class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-indigo-500 p-3">
                        <component :is="item.icon" class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ item.label }}</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold">{{ item.stat }}</p>
                    <p
                        :class="[item.changeType === 'increase' ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                        <ArrowUpIcon v-if="item.changeType === 'increase'"
                            class="h-5 w-5 flex-shrink-0 self-center text-green-500" aria-hidden="true" />
                        <ArrowDownIcon v-else class="h-5 w-5 flex-shrink-0 self-center text-red-500"
                            aria-hidden="true" />
                        <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by
                        </span>
                        {{ item.change }}
                    </p>
                    <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">View all<span
                                    class="sr-only"> {{ item.label }} stats</span></a>
                        </div>
                    </div>
                </dd>
            </div>
        </dl>
    </div>
</template>

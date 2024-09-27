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
import Icon from '@/Components/Icon.vue'

library.add(faCheckCircle, faTimesCircle )

const props = defineProps<{
    pageHead: PageHeadingTS
    tabs: {
        current: string
        navigation: {}
    },
    title: string
    stats?: {}
    totm: {
        product: {

        }
        family: {

        }
        department: {

        }
    }
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
        <dl class="grid grid-cols-1 gap-2 lg:gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                v-for="(stat, index) in stats"
                :key="'stat' + index"
                :href="route(stat.route.name, stat.route.parameters)"
                :style="{color: stat.color}"
                class="isolate relative overflow-hidden rounded-lg bg-white hover:bg-gray-50 cursor-pointer border border-gray-200 px-4 py-5 shadow-sm sm:p-6 sm:pb-3"
            >
                <BackgroundBox class="-z-10 opacity-60 absolute top-0 right-0" />

                <dt class="truncate text-sm font-medium text-gray-400">
                    {{ stat.label }}
                </dt>

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
                    <component
                        v-for="meta in stat.metas"
                        :is="meta.href?.name ? Link : 'div'"
                        :href="meta.href?.name ? route(meta.href.name, meta.href.parameters) : ''"
                        class="group/sub px-2 flex gap-x-0.5 items-center font-normal"
                        v-tooltip="capitalize(meta.tooltip) || capitalize(meta.icon?.tooltip)"
                    >
                        <FontAwesomeIcon
                            aria-hidden="true"
                            :icon="meta.icon.icon"
                            class="md:opacity-50 group-hover/sub:opacity-100"
                            :class="meta.icon.class"
                            fixed-width
                        />
                        <div class="group-hover/sub:text-gray-700">
                            {{ locale.number(meta.count) }}
                        </div>
                    </component>
                </div>
            </Link>
        </dl>
    </div>

    <div class="p-6">
        <div class="text-xl font-semibold py-1 border-b border-gray-200">Top of the month (TotM)</div>
        <dl class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 sm:grid-rows-2 h-72">
            <div class="isolate relative group bg-gray-100 h-full row-span-2 rounded-md overflow-hidden px-8 py-8 flex gap-x-4">
                <div class="h-full flex flex-col gap-y-1">
                    <div class="aspect-square h-full w-fit rounded-md overflow-hidden">
                        <img src="https://www.ancientwisdom.biz/wi.php?id=1857494&s=705x705" class="h-full w-auto z-10" />
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-y-1">
                    <div>
                        <div class="text-indigo-600 text-sm animate-pulse">Product of the month</div>
                        <h3 class="text-xl font-semibold">
                            Vintage Orange Mint Hand Carved Buddha Statue - 30cm - Happy Buddha
                        </h3>
                        <div class="text-gray-400 text-sm">AT48441546</div>
                    </div>
                    <div>
                        <p aria-hidden="true" class="text-gray-500">Sold this month: 1,684 pcs</p>
                        <p aria-hidden="true" class="text-gray-500">Stock: 8,652</p>
                        <p aria-hidden="true" class="text-gray-500">Price: $62/pcs</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 rounded-md">
            </div>

            <div class="bg-gray-100 rounded-md">
            </div>
        </dl>
    </div>

    <!-- <pre>{{ totm.product }}</pre> -->


</template>

<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { useLocaleStore } from "@/Stores/locale"
import { capitalize } from 'vue'
import { useTruncate } from '@/Composables/useTruncate'
import { useRangeFromNow } from '@/Composables/useFormatTime'

const props = defineProps<{
    // label: string
    // count: number
    // cases: {
    //     [key: string]: {
    //         label: string
    //         count: number
    //         icon: {
    //             icon: string
    //             class: string
    //             tooltip: string
    //         }
    //     }
    // }
}>()

const pieData = [
    {
        label: 'Red',
        count: 12,
        cases: {
            pertamina: {
                label: 'Pertamina',
                count: 12,
                icon: {
                    icon: 'fa-gas-pump',
                    class: 'text-red-500',
                    tooltip: 'Pertamina'
                }
            },
            indihome: {
                label: 'Indihome',
                count: 31,
                icon: {
                    icon: 'fa-wifi',
                    class: 'text-red-500',
                    tooltip: 'Indihome'
                }
            },
        }
    },
    {
        label: 'Blue',
        count: 19,
        cases: {
            biznet: {
                label: 'Biznet',
                count: 19,
                icon: {
                    icon: 'fa-wifi',
                    class: 'text-blue-500',
                    tooltip: 'Biznet'
                }
            },
            demokrat: {
                label: 'Demokrat',
                count: 31,
                icon: {
                    icon: 'fa-flag-usa',
                    class: 'text-blue-500',
                    tooltip: 'Demokrat'
                }
            },
        }
    }
]

const options = {
    responsive: true,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            // Popup: When the data set is hovered
            // enabled: false,
            titleFont: {
                size: 10,
                weight: 'lighter'
            },
            bodyFont: {
                size: 11,
                weight: 'bold'
            }
        },
    }
}

const banner = [
    {
        id: 1,
        name: 'Banner 1',
        image: 'https://picsum.photos/200/300',
        views: 12,
        updated_at: '2021-08-01 12:00:00',
        state_icon: {
            icon: 'fa-check-circle',
            class: 'text-green-500',
            tooltip: 'Active'
        },
        route: {
            name: 'customer.banners.banners.show',
            parameters: {
                banner: 1
            }
        }
    }
]
</script>

<template>
    <div v-for="pie in pieData" class="px-4 py-5 sm:p-6 rounded-lg bg-white shadow tabular-nums">
        <dt class="text-base font-medium text-gray-400 capitalize">{{ pie.label }}</dt>
        <dd class="mt-2 flex justify-between gap-x-2">
            <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                <!-- In Total -->
                <div class="flex gap-x-2 items-end">
                    {{ useLocaleStore().number(pie.count) }}
                    <span class="text-sm font-medium leading-4 text-gray-500 ">{{ trans('in total') }}</span>
                </div>

                <!-- Statistic -->
                <div class="text-sm text-gray-500 flex gap-x-5 gap-y-1 items-center flex-wrap">
                    <div v-for="dCase in pie.cases" class="flex gap-x-0.5 items-center font-normal"
                        v-tooltip="capitalize(dCase.icon.tooltip)">
                        <FontAwesomeIcon :icon='dCase.icon.icon' :class='dCase.icon.class' fixed-width
                            :title="dCase.icon.tooltip" aria-hidden='true' />
                        <span class="font-semibold">
                            {{ useLocaleStore().number(dCase.count) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Donut -->
            <div class="w-20">
                <Pie :data="{
                    labels: Object.entries(cases).map(([, value]) => value.label),
                    datasets: [{
                        data: Object.entries(cases).map(([, value]) => value.count),
                        hoverOffset: 4
                    }]
                }" :options="options" />
            </div>

        </dd>
    </div>

    <!-- Recently added Banner -->
    <div class="max-w-2xl lg:mx-0 lg:max-w-none">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-700 leading-none">{{ trans('Recently edited banner') }}</h2>
            <Link :href="route('customer.banners.banners.index')" class="text-sm text-gray-500 hover:text-gray-700">
                View all<span class="sr-only">, banner</span>
            </Link>
        </div>

        <!-- Looping: Last Edited Banners -->
        <ul  role="list" class="mt-3 grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3 xl:gap-x-8">
            <Link :href="`${route(lastEditedBanner.route?.name, lastEditedBanner.route?.parameters)}`" v-for="lastEditedBanner in banner" :key="lastEditedBanner.id" class="overflow-hidden rounded-md ring-1 h-fit ring-gray-300 hover:ring-2 hover:ring-gray-400">
                <div class="h-auto aspect-[4/1] flex items-center justify-center gap-x-4 border-b border-gray-700/5 bg-gray-200 overflow-hidden">
                    <Image :src="lastEditedBanner.image" :alt="lastEditedBanner?.name" />
                </div>
                <dl class="divide-y divide-transparent px-4 pt-1 pb-3 text-sm">
                    <!-- Title Banner -->
                    <div class="flex justify-between items-center gap-x-4">
                        <!-- <dt class="text-gray-500 text-sm">{{ trans('Name') }}</dt> -->
                        <dd class="flex items-start gap-x-2">
                            <div class="text-lg font-semibold text-gray-600">{{ useTruncate(lastEditedBanner?.name, 28, 4) }}</div>
                        </dd>
                    </div>

                    <!-- Last Eedited -->
                    <div class="flex justify-between items-center gap-x-4">
                        <!-- <dt class="text-gray-500 text-sm">{{ trans('Last edit') }}</dt> -->
                        <dd class="text-gray-600 text-xs italic tracking-wide space-x-1">
                            <FontAwesomeIcon fixed-width icon='fal fa-history' class='text-gray-400' aria-hidden='true' />
                            <span class="text-gray-500">{{ trans('Last edited on') }}</span>
                            <time :datetime="lastEditedBanner.updated_at">{{ useRangeFromNow(lastEditedBanner.updated_at, { localeCode: useLocaleStore().language.code }) }}</time>
                        </dd>
                        <div>
                            <FontAwesomeIcon fixed-width :icon='lastEditedBanner.state_icon?.icon' :class='lastEditedBanner.state_icon?.class' class="" aria-hidden='true' :alt="lastEditedBanner.state_icon?.tooltip"/>
                        </div>
                    </div>

                    <!-- Views -->
                    <div class="text-gray-500 text-xs italic space-x-1">
                        <FontAwesomeIcon fixed-width icon='far fa-eye' class='text-gray-400' aria-hidden='true' />
                        {{ lastEditedBanner.views ?? 0 }} views
                    </div>
                </dl>
            </Link>
        </ul>
    </div>
</template>
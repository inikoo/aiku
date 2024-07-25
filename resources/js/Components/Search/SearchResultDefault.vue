<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPallet } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
import { routeType } from '@/types/route'
import { Icon } from '@/types/Utils/Icon'
import { useFormatTime } from '@/Composables/useFormatTime'
import { inject, ref } from 'vue'
import AddressLocation from '@/Components/Elements/Info/AddressLocation.vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import LoadingText from '@/Components/Utils/LoadingText.vue'
library.add(faPallet)

const props = defineProps<{
    data?: {
        tooltip?: []
        route?: routeType
        container?: {
            key?: string
            icon: string | string[]
            label: string
            href?: routeType
            tooltip: string
        }
        noCapitalise?: boolean
        icon: {
            icon: string | string[]
            title: string
            tooltip?: string
        }
        iconRight?: {
            tooltip?: string
            icon: string
            class: string
        }
        model: string
        title: string
        afterTitle?: {
            label: string
            class?: string
        }
        meta?: {
            key: string
            type?: string // 'date', 'amount'
            code?: string // 'GBP',
            amount?: number  // 30.40, 85
            label?: string
            number?: number | string
            icon?: Icon
            leftIcon?: Icon
            href?: routeType
            tooltip?: string
        }[]
    }
    modelType: string
}>()

const emits = defineEmits<{
    (e: 'finishVisit', value: false): void
}>()

const locale = inject('locale', {})

const isLoading = ref(false)

</script>

<template>
    <component
        :is="data?.route?.name ? Link : 'div'"
        as="a"
        :href="data?.route?.name ? route(data?.route.name, data?.route.parameters) : ''"
        class="flex gap-x-2 items-center"
        @start="() => isLoading = true"
        @finish="() => emits('finishVisit', false)"
    >
        <div v-if="isLoading" class="absolute inset-0 bg-black/50 flex flex-col gap-y-4 justify-center items-center text-white cursor-default">
            <LoadingIcon class="text-6xl"/>
            <LoadingText />
        </div>

        <div class="flex leading-none py-1 items-start gap-x-3 tracking-tight ">
            <div v-if="data?.icon" v-tooltip="modelType" class="border-[2px] border-gray-400 text-gray-500 rounded h-10 aspect-square flex items-center justify-center">
                <FontAwesomeIcon
                    aria-hidden="true"
                    :icon="data?.icon?.icon || data?.icon"
                    fixed-width />
            </div>

            <div class="">
                <div v-if="data?.container" v-tooltip="data?.container?.tooltip" class="w-fit mb-1 text-xs text-gray-400 flex items-end leading-none">
                    <template v-if="data?.container?.key === 'address'">
                        <AddressLocation :data="data?.container?.label" />
                    </template>
                    
                    <template v-else>
                        {{ data?.container?.label }}
                    </template>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-y-1.5 gap-x-3 font-semibold">
                    <h2 :class="data?.noCapitalise ? '' : 'capitalize'" class="text-xl">
                        <span v-if="data?.model" class="text-gray-400 mr-2 font-medium block sm:inline">{{ data?.model }}</span>
                        <span class="inline-block whitespace-nowrap">{{ data?.title }}</span>
                    </h2>

                    <!-- Section: After Title -->
                    <div class="flex gap-x-2 items-center">
                        <FontAwesomeIcon v-if="data?.iconRight" v-tooltip="data?.iconRight.tooltip || ''"
                            :icon="data?.iconRight.icon" class="h-4" :class="data?.iconRight.class" aria-hidden="true" />
                        <div v-if="data?.afterTitle" class="text-gray-400 font-normal text-base leading-none">
                            {{ data?.afterTitle.label }}
                        </div>
                    </div>
                </div>
                
                <!-- Section: mini Tabs -->
                <div v-if="data?.meta?.length" class="flex sm:flex-wrap sm:gap-y-0.5 text-sm">
                    <template v-for="meta in data?.meta">
                        <div v-tooltip="meta.tooltip" class="flex items-center text-gray-400">
                            <FontAwesomeIcon v-if="meta.icon" :icon='meta.icon' class='' fixed-width aria-hidden='true' />
                            <template v-if="meta.type === 'date'">{{ useFormatTime(meta.label) }}</template>
                            <template v-else-if="meta.type === 'amount'">{{ meta.label }} {{ locale.currencyFormat(meta.code, meta.amount) }}</template>
                            <template v-else-if="meta.type === 'number'">{{ meta.label }} {{ locale.number(meta.number) }}</template>
                            <template v-else>{{ meta.label }}</template>
                        </div>
                        <div class="last:hidden px-2">
                            •
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </component>
</template>
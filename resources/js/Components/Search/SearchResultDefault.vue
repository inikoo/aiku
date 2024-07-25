<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPallet } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
import { routeType } from '@/types/route'
import Container from '@/Components/Headings/Container.vue'
import { Icon } from '@/types/Utils/Icon'
import { useFormatTime } from '@/Composables/useFormatTime'
library.add(faPallet)

const props = defineProps<{
    data?: {
        container?: {
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
            tooltip: string
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
            label?: string
            number?: number | string
            leftIcon?: Icon
            href?: routeType
        }[]
    }
}>()

</script>

<template>
    <Link as="a" href="" class="relative flex gap-x-2 items-center ">
    <div class="flex leading-none py-1 items-start gap-x-2 tracking-tight ">
        <div v-if="data?.icon" class="border-[2px] border-gray-600 text-gray-600 rounded h-10 aspect-square flex items-center justify-center">
            <FontAwesomeIcon
                v-tooltip="data?.icon.tooltip || ''"
                aria-hidden="true"
                :icon="data?.icon.icon || data?.icon"
                size="sm" fixed-width />
        </div>

        <div class="">
            <div v-if="data?.container" class="mb-1 text-xs text-gray-400 flex items-end leading-none">
                <Container :data="data?.container" />
            </div>
            
            <div class="flex flex-col sm:flex-row gap-y-1.5 gap-x-3 font-semibold">
                <h2 :class="data?.noCapitalise ? '' : 'capitalize'" class="text-xl">
                    <span v-if="data?.model" class="text-gray-400 mr-2 font-medium block sm:inline">{{ data?.model }}</span>
                    <span class="inline-block">{{ data?.title }}</span>
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
                    <div class="flex items-center text-gray-400">
                        <template v-if="meta.key === 'created_date'">{{ useFormatTime(meta.label) }}</template>
                        <template v-else>{{ meta.label }}</template>
                    </div>
                    <div class="last:hidden px-1">
                        •
                    </div>
                </template>
            </div>
        </div>
    </div>
    </Link>
</template>
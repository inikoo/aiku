<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sun, 30 Oct 2022 15:27:23 Greenwich Mean Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { inject, ref } from 'vue'
import { routeType } from '@/types/route'
import { Link } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'

const props = defineProps<{
    style?: string | object
    size?: string
    icon?: string | string[]
    iconRight?: string | string[]
    action?: string
    label?: string
    full?: boolean
    capitalize?: boolean
    tooltip?: string
    loading?: boolean
    type?: string
    disabled?: boolean
    noHover?: boolean
    routeTarget?: routeType
    bindToLink: {
        preserveScroll?: boolean
        preserveState?: boolean
    }
}>()


const isLoadingVisit = ref(false)
</script>

<template>
    <component
        :is="props.routeTarget ? Link : 'div'"
        :href="props.routeTarget?.name ? route(props.routeTarget?.name, props.routeTarget?.parameters) : '#'"
        @start="() => isLoadingVisit = true"
        @finish="() => isLoadingVisit = false"
        :method="props.routeTarget?.method || undefined"
        :data="props.routeTarget?.body"
        v-bind="bindToLink"
    >
        <!-- Don't use v-bind make 'style' return empty object -->
        <Button
            :style
            :size
            :icon
            :iconRight
            :action
            :label
            :full
            :capitalize
            :tooltip
            :loading="isLoadingVisit || props.loading"
            :type
            :disabled
            :noHover
        />
    </component>
</template>
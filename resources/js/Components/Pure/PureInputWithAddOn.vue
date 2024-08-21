<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const model = defineModel()

const props = defineProps<{
    leftAddOn?: {
        label?: string
        icon?: string | string[]
    }
    rightAddOn?: {
        label?: string
        icon?: string | string[]
    }
    placeholder?: string
    readonly?: boolean
    inputName?: string
}>()

defineOptions({
    inheritAttrs: false
})
</script>

<template>
    <div
        class="relative flex items-center justify-between rounded-md px-3 shadow-sm ring-1 ring-inset ring-gray-300 sm:max-w-md"
        :class="[readonly ? 'focus-within:ring-1 bg-gray-100' : 'focus-within:ring-2 focus-within:ring-inset focus-within:ring-gray-500']"
    >
        <div class="flex w-full">
            <div v-if="leftAddOn" class="flex items-center gap-x-1.5">
                <div class="flex select-none items-center text-gray-400 sm:text-sm">
                    <FontAwesomeIcon v-if="leftAddOn.icon" :icon="leftAddOn.icon" fixed-width aria-hidden="true" />
                    <span v-if="leftAddOn.label" class="leading-none">{{ leftAddOn.label }}</span>
                </div>
            </div>
            <input
                v-model="model"
                v-bind="$attrs"
                type="text"
                class="h-full w-full border-transparent focus:border-transparent bg-transparent py-1.5 px-2 placeholder:text-gray-400 read-only:text-gray-600 focus:ring-0 sm:text-sm sm:leading-6"
                :placeholder="placeholder || 'Enter value'"
                :readonly="readonly"
            />
        </div>

        <!-- Add On: Right -->
        <div v-if="rightAddOn" class="h-full flex items-center gap-x-1.5">
            <div class="flex select-none items-center text-gray-400 sm:text-sm">
                <FontAwesomeIcon v-if="rightAddOn.icon" :icon="rightAddOn.icon" fixed-width aria-hidden="true" />
                <span v-if="rightAddOn.label" class="leading-none">{{ rightAddOn.label }}</span>
            </div>
        </div>

        <!-- Slot: to add icon state (success, fail, loading) on FieldForm -->
        <slot />
    </div>
</template>
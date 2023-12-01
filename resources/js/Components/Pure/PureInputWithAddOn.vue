<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps<{
    modelValue: string
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

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
    <div
        class="relative flex rounded-md px-3 shadow-sm ring-1 ring-inset ring-gray-300 sm:max-w-md"
        :class="[readonly ? 'focus-within:ring-1 bg-gray-100' : 'focus-within:ring-2 focus-within:ring-inset focus-within:ring-gray-500']"
    >
        <div v-if="leftAddOn" class="flex items-center gap-x-1.5">
            <div class="flex select-none items-center text-gray-400 sm:text-sm">
                <FontAwesomeIcon v-if="leftAddOn.icon" :icon="leftAddOn.icon" aria-hidden="true" />
                <span v-if="leftAddOn.label" class="leading-none mb-0.5">{{ leftAddOn.label }}</span>
            </div>
        </div>

        <input :value="modelValue" @input="(e: any) => emits('update:modelValue', e.target.value)" type="text" :name="inputName ?? 'inputWithAddOn'" :id="inputName ?? 'inputWithAddOn'"
            class="block flex-1 border-0 bg-transparent py-1.5 px-1 mb-0.5 leading-none placeholder:text-gray-400 read-only:text-gray-600 focus:ring-0 sm:text-sm sm:leading-6"
            :placeholder="placeholder ?? 'Enter value'" :readonly="readonly" />

        <!-- Add On: Right -->
        <div v-if="rightAddOn" class="flex items-center gap-x-1.5">
            <div class="flex select-none items-center text-gray-400 sm:text-sm">
                <FontAwesomeIcon v-if="rightAddOn.icon" :icon="rightAddOn.icon" aria-hidden="true" />
                <span v-if="rightAddOn.label" class="leading-none mb-0.5">{{ rightAddOn.label }}</span>
            </div>
        </div>

        <!-- Slot: to add icon state (success, fail, loading) on FieldForm -->
        <slot />
    </div>
</template>
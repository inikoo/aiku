<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps<{
    modelValue: string
    placeholder?: string
    readonly?: boolean
    inputName?: string
    counter?: boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
    <div class="rounded-md shadow-sm">
        <textarea
            :value="modelValue"
            @input="(event: any) => emits('update:modelValue', event.target.value)"
            :id="inputName"
            :name="inputName"
            :placeholder="placeholder"
            rows="3"
            class="block w-full rounded-md shadow-sm text-gray-600 border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
    </div>
    <div v-if="counter" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            Letters: {{ modelValue.length }}
        </p>
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            Words: {{ modelValue.trim().split(/\s+/).filter(Boolean).length }}
        </p>
    </div>
</template>
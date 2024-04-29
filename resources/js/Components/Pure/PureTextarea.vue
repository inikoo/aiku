<script setup lang="ts">
// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'

defineOptions({
    inheritAttrs: false
})

const props = defineProps<{
    modelValue: string
    placeholder?: string
    counter?: boolean
    full?: boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
    <div class="rounded-md shadow-sm" :class="full ? 'w-full' : ''">
        <textarea
            :value="modelValue"
            @input="(event: any) => emits('update:modelValue', event.target.value)"
            v-bind="$attrs"
            :placeholder="placeholder || trans('Enter text here')"
            class="block w-full rounded-md shadow-sm text-gray-600 placeholder:text-gray-400 placeholder:italic placeholder:text-xs border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
    </div>
    
    <div v-if="counter" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start tabular-nums">
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            {{ trans('Letters') }}: {{ modelValue.length }}<span v-if="$attrs.maxLength">/{{ $attrs.maxLength }}</span>
        </p>
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            {{ trans('Words') }}: {{ modelValue.trim().split(/\s+/).filter(Boolean).length }}
        </p>
    </div>
</template>
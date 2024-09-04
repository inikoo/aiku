<script setup lang="ts">
// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'

import { faSpinnerThird } from '@fad'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faSpinnerThird)

defineOptions({
    inheritAttrs: false
})

const props = defineProps<{
    modelValue: string
    placeholder?: string
    counter?: boolean
    full?: boolean
    disabled?: boolean
    loading?: boolean
    rows?:Number|null
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
    <div class="relative rounded-lg" :class="full ? 'w-full' : ''">
        <textarea
            :value="modelValue"
            @input="(event: any) => emits('update:modelValue', event.target.value)"
            v-bind="$attrs"
            :disabled="disabled" 
            :rows="rows"
            :placeholder="placeholder || trans('Enter text here')" class="block w-full rounded-md placeholder:text-gray-400 placeholder:italic placeholder:text-xs 
                   disabled:text-gray-500 disabled:border-gray-300 disabled:bg-gray-100
                   border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
        <!-- Loading icon -->
        
        <div class="absolute bottom-2 right-2 flex items-center justify-center">
            <slot name='stateIcon'>
                <FontAwesomeIcon v-if="loading" :icon="faSpinnerThird" />
            </slot>
        </div>
    </div>
    <div v-if="counter" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start tabular-nums">
        <p class="">
            {{ trans('Letters') }}: {{ modelValue.length }}<span v-if="$attrs.maxLength">/{{ $attrs.maxLength }}</span>
        </p>
        <p class="">
            {{ trans('Words') }}: {{ modelValue.trim().split(/\s+/).filter(Boolean).length }}
        </p>
    </div>
</template>
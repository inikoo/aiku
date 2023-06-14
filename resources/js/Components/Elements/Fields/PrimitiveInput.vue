<script setup lang="ts">
import { ref } from 'vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationCircle, faCheckCircle } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationCircle, faCheckCircle);

const props = defineProps<{
    modelValue?: string,
    showStats: Boolean,
    type: string,
    form: Object,
    fieldName: string,
    placeholder?: string,
}>()
defineEmits(['update:modelValue'])
const inputValue = ref(props.modelValue ? props.modelValue : '')

const handleChange = (form) => {
    if (form.fieldType === 'edit') {
        form.clearErrors()
    }
}

</script>

<template>
    <div class="relative">
        <input :type="type" v-model.trim="inputValue" @input="$emit('update:modelValue', inputValue), handleChange(form)"
            :placeholder="placeholder" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
        <div v-if="form.errors[fieldName] || form.recentlySuccessful"
            class="absolute inset-y-2/4 right-0 pr-3 flex items-center pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500"
                aria-hidden="true" />
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful"
                class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true" />
        </div>
    </div>
    <div v-if="showStats" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
        <p class="">
            Letters: {{ inputValue.length }}
        </p>
        <p class="">
            Words: {{ inputValue.trim().split(/\s+/).filter(Boolean).length }}
        </p>
    </div>
</template>
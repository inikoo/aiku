<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{
    modelValue?: string,
    showStats: Boolean,
    type: string,
    form: object,
}>()
defineEmits(['update:modelValue'])
const inputValue = ref(props.modelValue ? props.modelValue : '')

const handleChange = (form) => {
    if (form.type === 'edit') {
        form.clearErrors()
    }
}

</script>

<template>
    <input :type="type" v-model.trim="inputValue" @input="$emit('update:modelValue', inputValue), handleChange(form)"
        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
    <div v-if="showStats" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            Letters: {{ inputValue.length }}
        </p>
        <p class="">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
            Words: {{ inputValue.trim().split(/\s+/).filter(Boolean).length }}
        </p>
    </div>
</template>
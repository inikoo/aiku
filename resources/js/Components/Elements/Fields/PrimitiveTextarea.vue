<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{
    modelValue?: string,
    showStats: Boolean,
}>()
defineEmits(['update:modelValue'])
const textareaValue = ref(props.modelValue ? props.modelValue : '')
</script>

<template>
    <div>
        <label for="about" class="block text-sm font-medium text-gray-700">
            <!-- {{ pageBody.layout.profile.fields.about.label }} -->
        </label>
        <div class="mt-1  rounded-md shadow-sm">
            <textarea id="about" name="about" rows="3" v-model.trim="textareaValue" @input="$emit('update:modelValue', textareaValue)"
                placeholder="Tell us about yourself"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm" />
        </div>
        <div v-if="showStats" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
            <p class="">
                <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
                Letters: {{ textareaValue.length }}
            </p>
            <p class="">
                <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
                Words: {{ textareaValue.trim().split(/\s+/).filter(Boolean).length }}
            </p>
        </div>
    </div>
</template>

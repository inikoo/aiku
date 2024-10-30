<script setup lang="ts">
import { computed, watch } from 'vue'
import BorderProperty from '@/Components/Workshop/Properties/BorderProperty.vue'
import { trans } from 'laravel-vue-i18n'

const model = defineModel()

const compModel = computed(() => {
    // To check does the data if changed
    return JSON.stringify(model.value)
})

const emit = defineEmits();
watch(compModel, () => {
    console.log('on change compModel')
    emit('update:modelValue', model.value)
})


</script>

<template>
    <div v-if="model"  class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Border') }}</div>
        <BorderProperty v-model="model" />
    </div>
</template>

<style scoped></style>

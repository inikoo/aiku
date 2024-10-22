<script setup lang="ts">
import { computed, watch } from 'vue'
import PaddingMarginProperty from '@/Components/Websites/Fields/Properties/PaddingMarginProperty.vue'
import { trans } from 'laravel-vue-i18n'

const model = defineModel()

const compModel = computed(() => {
    // To check does the data if changed
    return JSON.stringify(model.value)
})

const emit = defineEmits();
watch(compModel, () => {
    emit('update:modelValue', model.value)
})


</script>

<template>
    <div v-if="model" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Padding') }}</div>
        <PaddingMarginProperty v-model="model" />
    </div>
</template>

<style scoped></style>

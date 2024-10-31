<script setup lang="ts">
import { computed, watch } from 'vue'
import BackgroundProperty from '@/Components/Workshop/Properties/BackgroundProperty.vue'
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    uploadImageRoute?: routeType
}>()

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
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div>
        <BackgroundProperty v-model="model" :uploadImageRoute="uploadImageRoute"/>
    </div>
</template>

<style scoped></style>

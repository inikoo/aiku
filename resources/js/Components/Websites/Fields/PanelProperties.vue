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
    console.log('on change compModel')
    emit('update:modelValue', model.value)
})


</script>

<template>
    <PaddingMarginProperty
        v-if="model.padding"
        v-model="model"
        :label="trans('Padding')"
        scope="padding"
    />

    <PaddingMarginProperty
        v-if="model.margin"
        v-model="model"
        :label="trans('Margin')"
        scope="margin"
    />

    <!-- Properties: Padding -->
    
</template>

<style scoped></style>

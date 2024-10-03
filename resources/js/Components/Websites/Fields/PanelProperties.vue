<script setup lang="ts">
import { computed, watch } from 'vue'
import PaddingMarginProperty from '@/Components/Websites/Fields/Properties/PaddingMarginProperty.vue'
import BackgroundProperty from '@/Components/Websites/Fields/Properties/BackgroundProperty.vue'
import BorderProperty from '@/Components/Websites/Fields/Properties/BorderProperty.vue'
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
    <div>
        <!-- <pre>{{ model?.border }}</pre> -->
    </div>


    <div v-if="model?.background" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div>
        
        <BackgroundProperty
            v-model="model.background"
        />
    </div>

    <div v-if="model?.border" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Border') }}</div>
        
        <BorderProperty
            v-model="model.border"
        />
    </div>

    <div v-if="model?.padding" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Padding') }}</div>
        
        <PaddingMarginProperty
            v-model="model.padding"
        />
    </div>

    <div v-if="model?.margin" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Margin') }}</div>
        
        <PaddingMarginProperty
            v-model="model.margin"
        />
    </div>

</template>

<style scoped></style>

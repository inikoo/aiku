<script setup lang="ts">
import { computed, watch } from 'vue'
import PaddingMarginProperty from '@/Components/Websites/Fields/Properties/PaddingMarginProperty.vue'
import BackgroundProperty from '@/Components/Websites/Fields/Properties/BackgroundProperty.vue'
import BorderProperty from '@/Components/Websites/Fields/Properties/BorderProperty.vue'
import TextProperty from '@/Components/Websites/Fields/Properties/TextProperty.vue'
import DimensionProperty from '@/Components/Websites/Fields/Properties/DimensionProperty.vue'
import ButtonsProperty from '@/Components/Websites/Fields/Properties/ButtonsProperty.vue'
import { trans } from 'laravel-vue-i18n'
import PureInput from '@/Components/Pure/PureInput.vue'

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
    <div v-if="model?.text" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Text') }}</div>
        <div class="px-3">
            <PureInput v-model="model.text" />
        </div>
      
    </div>


    <div  class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Link') }}</div>
        <div class="px-3">
            <PureInput v-model="model.link" placeholder="https://"/>
        </div>
    </div>

    <div v-if="model?.container?.properties.dimension" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Dimension') }}</div>
        <DimensionProperty v-model="model.container.properties.dimension" />
    </div>

    <div v-if="model?.container?.properties?.background" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div>
        <BackgroundProperty v-model="model.container.properties.background" />
    </div>

    <div v-if="model?.container?.properties?.text" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Text') }}</div>
        <TextProperty v-model="model.container.properties.text" />
    </div>

    <div v-if="model?.container?.properties?.border" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Border') }}</div>

        <BorderProperty v-model="model.container.properties.border" />
    </div>

    <div v-if="model?.container?.properties?.padding" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Padding') }}</div>

        <PaddingMarginProperty v-model="model.container.properties.padding" />
    </div>

    <div v-if="model?.container?.properties?.margin" class="border-t border-gray-300 bg-gray-100">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Margin') }}</div>
        <PaddingMarginProperty v-model="model.container.properties.margin" />
    </div>

</template>

<style scoped></style>

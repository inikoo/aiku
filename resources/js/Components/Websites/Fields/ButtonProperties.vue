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
import ColorPicker from '@/Components/Utils/ColorPicker.vue'
import Link from '@/Components/Websites/Fields/Link.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faText } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faText)

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
        <div class="px-3 flex gap-x-2 flex-nowrap">
            <PureInput v-model="model.text" />
            <div v-if="model?.container?.properties?.text?.color" class="h-full">
                <ColorPicker
                    :color="model?.container?.properties?.text.color"
                    @changeColor="(newColor) => model.container.properties.text.color = `rgba(${newColor.rgba.r}, ${newColor.rgba.g}, ${newColor.rgba.b}, ${newColor.rgba.a})`"
                    closeButton>
                    <template #button>
                        <div v-bind="$attrs"
                            class="bg-gray-100 overflow-hidden h-full aspect-square w-10 rounded-md border border-gray-300 cursor-pointer flex justify-center items-center"
                            >
                            <FontAwesomeIcon icon='far fa-text' class='' fixed-width aria-hidden='true'
                                :style="{
                                    color: `${model.container.properties.text.color}`
                                }"
                            />
                        </div>
                    </template>
                </ColorPicker>
            </div>
        </div>
    </div>
                

    <div  class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Link') }}</div>
        <div class="px-3">
            <Link v-model="model.link" />
            <!-- <PureInput v-model="model.link" /> -->
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

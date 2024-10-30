<script setup lang="ts">
import { ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
import PaddingMarginProperty from '@/Components/Workshop/Properties/PaddingMarginProperty.vue'
import BackgroundProperty from '@/Components/Workshop/Properties/BackgroundProperty.vue'
import BorderProperty from '@/Components/Workshop/Properties/BorderProperty.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorForm.vue"

const props = defineProps<{
    modelValue: any,
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

</script>

<template>
    <div class="mt-8">
        <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Text') }}</div>
        <Editor v-model="modelValue.text" />
    </div>

    <div class="mt-8">

        <div v-if="modelValue?.properties.background" class="border-t border-gray-300  pb-3">
            <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Background') }}</div>

            <BackgroundProperty v-model="modelValue.properties.background" />
        </div>

        <div v-if="modelValue?.properties?.border" class="border-t border-gray-300">
            <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Border') }}</div>

            <BorderProperty v-model="modelValue.properties.border" />
        </div>

        <div v-if="modelValue?.properties?.padding" class="border-t border-gray-300">
            <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Padding') }}</div>

            <PaddingMarginProperty v-model="modelValue.properties.padding" />
        </div>

        <div v-if="modelValue?.properties?.margin" class="border-t border-gray-300">
            <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Margin') }}</div>

            <PaddingMarginProperty v-model="modelValue.properties.margin" />
        </div>
    </div>
</template>


<style lang="scss" scoped></style>

<script setup lang="ts">
import { onBeforeMount } from 'vue'
import PaddingMarginProperty from '@/Components/Workshop/Properties/PaddingMarginProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set, get } from 'lodash'


const model = defineModel<typeof localModel>()

const emit = defineEmits(['update:modelValue']);

// Create a local copy of the model for internal use
const localModel = {
    unit: "px",
    top: {
        value: null
    },
    left: {
        value: null
    },
    right: {
        value: null
    },
    bottom: {
        value: null
    }
}

onBeforeMount(() => {
    if (!model.value?.unit) {
        set(model, 'value.unit', localModel.unit)
    }
    if (!model.value?.top?.value) {
        set(model, 'value.top.value', localModel.top.value)
    }
    if (!model.value?.left?.value) {
        set(model, 'value.left.value', localModel.left.value)
    }
    if (!model.value?.right?.value) {
        set(model, 'value.right.value', localModel.right.value)
    }
    if (!model.value?.bottom?.value) {
        set(model, 'value.bottom.value', localModel.bottom.value)
    }
})

</script>

<template>
    <div class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Margin') }}</div>
        <PaddingMarginProperty :modelValue="model || localModel" @update:modelValue="(e) => model = e" :scope="trans('Margin')" />
    </div>
</template>

<style scoped></style>

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

// Initialize localModel with the prop value on mount
onBeforeMount(() => {
    if (model.value) {
        set(model, 'value', localModel)
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

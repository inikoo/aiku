<script setup lang="ts">
import { ref, watch, computed, onBeforeMount, toRaw } from 'vue'
import DimensionProperty from '@/Components/Workshop/Properties/DimensionProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set } from 'lodash'

const model = defineModel<typeof localModel>()

// Local copy of the model for two-way binding
const localModel = {
    height: { value: null, unit: 'px' },
    width: { value: null, unit: '%' },
}

// Sync with the prop value initially
onBeforeMount(() => {
    if (!model.value?.height) {
        console.log('============= height')
        set(model.value, 'height', localModel.height)
    }

    if (!model.value?.width) {
        console.log('============= width')
        set(model.value, 'width', localModel.width)
    }
})

</script>

<template>
    <div class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Dimension') }}</div>
        <DimensionProperty v-model="model" />
    </div>
</template>

<style scoped></style>

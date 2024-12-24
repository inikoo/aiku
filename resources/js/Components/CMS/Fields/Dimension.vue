<script setup lang="ts">
import { onMounted, inject } from 'vue'
import DimensionProperty from '@/Components/Workshop/Properties/DimensionProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set } from 'lodash'

const model = defineModel<typeof localModel>()

const onSaveWorkshopFromId: Function = inject('onSaveWorkshopFromId', (e?: number) => { console.log('onSaveWorkshopFromId not provided') })
const side_editor_block_id = inject('side_editor_block_id', () => { console.log('side_editor_block_id not provided') })  // Get the block id that use this property

// Local copy of the model for two-way binding
const localModel = {
    height: { value: null, unit: 'px' },
    width: { value: null, unit: '%' },
}

// Sync with the prop value initially
onMounted(() => {
    if (!model.value?.height && model.value?.height !== localModel.height) {
        set(model.value, 'height', localModel.height)
        onSaveWorkshopFromId(side_editor_block_id, 'dimension value.height')
    }

    if (!model.value?.width && model.value?.width !== localModel.height) {
        set(model.value, 'width', localModel.width)
        onSaveWorkshopFromId(side_editor_block_id, 'dimension value.width')
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

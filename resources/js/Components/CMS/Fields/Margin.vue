<script setup lang="ts">
import { inject, onBeforeMount, onMounted } from 'vue'
import PaddingMarginProperty from '@/Components/Workshop/Properties/PaddingMarginProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set, get } from 'lodash'


const model = defineModel<typeof localModel>()
// const emits = defineEmits<{
//     (e: 'update:modelValue'): void
// }>()

const onSaveWorkshopFromId: Function = inject('onSaveWorkshopFromId', (e?: number) => { console.log('onSaveWorkshopFromId not provided') })
const side_editor_block_id = inject('side_editor_block_id', () => { console.log('side_editor_block_id not provided') })  // Get the block id that use this property

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


onMounted(async () => {

    if (!model.value?.unit && model.value?.unit !== localModel.unit ) {
        set(model, 'value.unit', localModel.unit)
        onSaveWorkshopFromId(side_editor_block_id, 'margin value.unit')
    }
    if (!model.value?.top?.value && model.value?.top?.value !== localModel.top?.value ) {
        set(model, 'value.top.value', localModel.top.value)
        onSaveWorkshopFromId(side_editor_block_id, 'margin value.top.value')
    }
    if (!model.value?.left?.value && model.value?.left?.value !== localModel.left?.value ) {
        set(model, 'value.left.value', localModel.left.value)
        onSaveWorkshopFromId(side_editor_block_id, 'margin value.left.value')
    }
    if (!model.value?.right?.value && model.value?.right?.value !== localModel.right?.value ) {
        set(model, 'value.right.value', localModel.right.value)
        onSaveWorkshopFromId(side_editor_block_id, 'margin value.right.value')
    }
    if (!model.value?.bottom?.value && model.value?.bottom?.value !== localModel.bottom?.value ) {
        set(model, 'value.bottom.value', localModel.bottom.value)
        onSaveWorkshopFromId(side_editor_block_id, 'margin value.bottom.value')
    }
})

</script>

<template>
    <div>
        <PaddingMarginProperty :modelValue="model || localModel" @update:modelValue="(e) => model = e" :scope="trans('Margin')" />
    </div>
</template>

<style scoped></style>

<script setup lang="ts">
import { onMounted, inject } from 'vue'
import DimensionProperty from '@/Components/Workshop/Properties/DimensionProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set } from 'lodash-es'

const model = defineModel<typeof localModel>()


const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()


const onSaveWorkshopFromId: Function = inject('onSaveWorkshopFromId', (e?: number) => { console.log('onSaveWorkshopFromId not provided') })
const side_editor_block_id = inject('side_editor_block_id', () => { console.log('side_editor_block_id not provided') })  // Get the block id that use this property

// Local copy of the model for two-way binding
const localModel = {
    height: { value: null, unit: 'px' },
    width: { value: null, unit: '%' },
}

// Sync with the prop value initially
onMounted(() => {
    if (!model.value?.height && (model.value?.height !== localModel.height)) {
       /*  set(model.value, 'height', localModel.height) */
        model.value = { ...model.value, ...localModel.height}
        emits('update:modelValue', model.value)
       /*  onSaveWorkshopFromId(side_editor_block_id, 'dimension value.height') */
    }

    if (!model.value?.width && (model.value?.width !== localModel.width)) {
        model.value = { ...model.value, ...localModel.width}
        emits('update:modelValue', model.value)
     /*    onSaveWorkshopFromId(side_editor_block_id, 'dimension value.width') */
    }
})


</script>

<template>
    <div>
        <DimensionProperty v-model="model"/>
    </div>
</template>

<style scoped></style>

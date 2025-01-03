<script setup lang="ts">
import { onMounted, watch, computed, inject } from 'vue'
import BackgroundProperty from '@/Components/Workshop/Properties/BackgroundProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { routeType } from '@/types/route'
import { set } from 'lodash'

const props = defineProps<{
    uploadImageRoute?: routeType
}>()

// const compModel = computed(() => {
//     return JSON.stringify(model.value)
// })
const model = defineModel<typeof localModel>({
    required: true
})

// const emit = defineEmits();
// watch(compModel, () => {
//     emit('update:modelValue', model.value)
// })

const onSaveWorkshopFromId: Function = inject('onSaveWorkshopFromId', (e?: number) => { console.log('onSaveWorkshopFromId not provided') })
const side_editor_block_id = inject('side_editor_block_id', () => { console.log('side_editor_block_id not provided') })  // Get the block id that use this property


const localModel = {
    type: 'color',
    color: 'rgba(255, 255, 255, 1)',
    image: {
        original: null
    }
}

onMounted(() => {
    if (!model.value?.type && model.value?.type !== localModel.type) {
        set(model.value, 'type', localModel.type)
        onSaveWorkshopFromId(side_editor_block_id, 'dimension value.height')
    }

    if (!model.value?.color && model.value?.color !== localModel.color) {
        set(model.value, 'color', localModel.color)
        onSaveWorkshopFromId(side_editor_block_id, 'background')
    }

    if (!model.value?.image && model.value?.image !== localModel.image) {
        set(model.value, 'image', localModel.image)
        onSaveWorkshopFromId(side_editor_block_id, 'background')
    }
})

</script>

<template>
    <div class="">
<!--         <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div> -->
        <BackgroundProperty v-model="model" :uploadImageRoute="uploadImageRoute" />
    </div>
</template>

<style scoped></style>

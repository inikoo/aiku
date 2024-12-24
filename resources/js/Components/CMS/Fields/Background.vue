<script setup lang="ts">
import { onMounted, watch, computed } from 'vue'
import BackgroundProperty from '@/Components/Workshop/Properties/BackgroundProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { routeType } from '@/types/route';

const props = defineProps<{
    uploadImageRoute?: routeType
}>()

const compModel = computed(() => {
    return JSON.stringify(model.value)
})
const model = defineModel()

const emit = defineEmits();
watch(compModel, () => {
    emit('update:modelValue', model.value)
})


onMounted(() => {
    if (!model.value) {
        model.value = {
            type: null,
            color: null,
            image: {
                original: null
            }
        }
}})

</script>

<template>
    <div v-if="model" class="">
<!--         <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div> -->
        <BackgroundProperty v-model="model" :uploadImageRoute="uploadImageRoute" />
    </div>
</template>

<style scoped></style>

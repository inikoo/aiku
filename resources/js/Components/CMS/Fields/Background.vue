<script setup lang="ts">
import { onMounted } from 'vue'
import BackgroundProperty from '@/Components/Workshop/Properties/BackgroundProperty.vue'
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    uploadImageRoute?: routeType
}>()

const modelValue = defineModel()


const emit = defineEmits();

onMounted(() => {
    if (!modelValue.value) {
        modelValue.value = {
            type: null,
            color: null,
            image: {
                original: null
            }
        }
}})

</script>

<template>
    <div v-if="modelValue" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Background') }}</div>
        <BackgroundProperty v-model="modelValue" :uploadImageRoute="uploadImageRoute" />
    </div>
</template>

<style scoped></style>

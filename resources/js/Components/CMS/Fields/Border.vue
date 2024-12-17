<script setup lang="ts">
import { ref, watch, onMounted, onBeforeMount } from 'vue'
import BorderProperty from '@/Components/Workshop/Properties/BorderProperty.vue'
import { trans } from 'laravel-vue-i18n'
import { set, get } from 'lodash'


const model = defineModel<{}>()

// Create a local copy of the model for internal use
const localModel = {
    top: { value: 0 },
    left: { value: 0 },
    unit: 'px',
    color: null,
    right: { value: 0 },
    bottom: { value: 0 },
    rounded: {
        unit: 'px',
        topleft: { value: 0 },
        topright: { value: 0 },
        bottomleft: { value: 0 },
        bottomright: { value: 0 },
    },
}

// Initialize localModel with the prop value on mount
onBeforeMount(() => {
    if (model.value) {
        set(model, 'value', localModel)
    }
})

</script>

<template>
    <div v-if="localModel" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Border') }}</div>
        <BorderProperty :modelValue="model || localModel" @update:modelValue="(e) => model = e" />
    </div>
</template>

<style scoped></style>

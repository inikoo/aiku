<script setup lang="ts">
import ParentFieldSideEditor from '@/Components/Workshop/SideEditor/ParentFieldSideEditor.vue'
import { getFormValue, setFormValue } from '@/Composables/SideEditorHelper'
import {  get } from 'lodash'
import { watch } from 'vue'

import { routeType } from '@/types/route'
const props = defineProps<{
    blueprint: Array<{ key: string; label?: string; type?: string }>
    uploadImageRoute?: routeType
}>()

const modelValue = defineModel()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()



</script>

<template>
    <div v-for="form in blueprint" :key="form.key">
        <div v-if="form.type != 'hidden'">
            <div class="my-2 text-xs font-semibold">{{ get(form, 'label', '') }}</div>
            <ParentFieldSideEditor 
                :blueprint="form" 
                :modelValue="modelValue"
                @update:modelValue="newValue => emits('update:modelValue',newValue)"
            />
        </div>
    </div>
</template>


<style lang="scss" scoped></style>

<script setup lang="ts">
import { onMounted, provide, ref } from 'vue'

import Accordion from 'primevue/accordion'
import ParentFieldSideEditor from '@/Components/Workshop/SideEditor/ParentFieldSideEditor.vue'

import { getFormValue } from '@/Composables/SideEditorHelper'
import { set as setLodash, get, cloneDeep } from 'lodash'

import { routeType } from '@/types/route'

const props = defineProps<{
    blueprint: {
        name?: string
        key?: string | string[]
        replaceForm?: {
            name?: string
            key?: string | string[]
            props_data?: {
                defaultValue?: string | number | null
            }
            type?: string
            options?: {
                label: string
                value: string
            }
        }[]
    }[]
    uploadImageRoute?: routeType
    block?: {
        id: number
    }
}>()

const modelValue = defineModel()
const openPanel = ref(0)

provide('side_editor_block_id', props.block?.id)


const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>()

const setChild = (blueprint = [], data = {}) => {
    const result = { ...data }
    for (const form of blueprint) {
        getFormValues(form, result)
    }
    return result
}

const getFormValues = (form: any, data: any = {}) => {
    const keyPath = Array.isArray(form.key) ? form.key : [form.key]  // ["container", "properties", "title"]
    if (form.replaceForm) {
        const set = getFormValue(data, keyPath) || {}
        setLodash(data, keyPath, setChild(form.replaceForm, set))
    } else {
        if (!get(data, keyPath)) {
            setLodash(data, keyPath, get(form, ["props_data", 'defaultValue'], null))
        }
    }
}

const setFormValues = (blueprint = [], data = {}) => {
    for (const form of blueprint) {
        getFormValues(form, data)
    }

    return data
}

onMounted(() => {
    if(!modelValue.value){
        emits('update:modelValue', setFormValues(props.blueprint))
    }
})


</script>

<template>
    <div v-for="(field, index) of blueprint.filter((item) => item.type != 'hidden')">
        <Accordion class="w-full" v-model="openPanel">
            <ParentFieldSideEditor 
                :blueprint="field" 
                :uploadImageRoute="uploadImageRoute" 
                v-model="modelValue"
                :key="field.key"
                :index="index" 
            />
        </Accordion>
    </div>
</template>


<style lang="scss" scoped></style>

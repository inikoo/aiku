<script setup lang="ts">
import { onMounted, ref, toRaw } from 'vue'

import Accordion from 'primevue/accordion'
import ParentFieldSideEditor from '@/Components/Workshop/SideEditor/ParentFieldSideEditor.vue'

import { getFormValue } from '@/Composables/SideEditorHelper'
import { set as setLodash, get, cloneDeep } from 'lodash'

import { routeType } from '@/types/route'

const props = defineProps<{
    blueprint: []
    uploadImageRoute?: routeType
}>()
const modelValue = defineModel()
const openPanel = ref(0);

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const setChild = (blueprint = [], data = {}) => {
    const result = { ...data };
    for (const form of blueprint) {
        getFormValues(form, result);
    }
    return result;
};

const getFormValues = (form: any, data: any = {}) => {
    const keyPath = Array.isArray(form.key) ? form.key : [form.key];
    if (form.replaceForm) {
        const set = getFormValue(data, keyPath) || {};
        setLodash(data, keyPath, setChild(form.replaceForm, set));
    } else {
        if (!get(data, keyPath)) {
            setLodash(data, keyPath, get(form, ["props_data", 'defaultValue'], null));
        }
    }
};

const setFormValues = (blueprint = [], data = {}) => {
    for (const form of blueprint) {
        getFormValues(form, data);
    }
    return data;
};

onMounted(() => {
    emits('update:modelValue', setFormValues(props.blueprint, cloneDeep(modelValue.value)));
});


</script>

<template>
    <div v-for="(field, index) of blueprint.filter((item) => item.type != 'hidden')">
        <Accordion class="w-full" v-model="openPanel">
            <ParentFieldSideEditor 
                :blueprint="field" 
                :uploadImageRoute="uploadImageRoute" 
                v-model="modelValue"
                :key="field.key" @update:modelValue="e => emits('update:modelValue', e)" 
                :index="index" 
            />
        </Accordion>
    </div>
</template>


<style lang="scss" scoped></style>

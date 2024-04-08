<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Switch } from '@headlessui/vue'
import { isNull } from 'lodash'

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
    }
}>()

const emits = defineEmits()

const updateFormValue = (newValue) => {
    let target = props.form;
    if (Array.isArray(props.fieldName)) {
        set(target, props.fieldName, newValue);
    } else {
        target[props.fieldName] = newValue;
    }
    emits("update:form", target,newValue);
};

const setFormValue = (data: Object, fieldName: String) => {
    if (Array.isArray(fieldName)) {
        return getNestedValue(data, fieldName)
    } else {
        if (isNull(data[fieldName]) || data[fieldName] == ''){
            updateFormValue(false);
            return false
        } 
        else{
            return data[fieldName]
        } 
       
    }
}

const getNestedValue = (obj: Object, keys: Array) => {
    return keys.reduce((acc, key) => {
        if (acc && typeof acc === "object" && key in acc) return acc[key]
        return false
    }, obj)
};

const value = ref(setFormValue(props.form, props.fieldName));

watch(value, (newValue) => {
    // Update the form field value when the value ref changes
    updateFormValue(newValue);
    props.form.errors[props.fieldName] = ''
});



console.log(props.form, props.fieldName)
</script>
<template>
    <div>
        <Switch v-model="value" :class="value ? 'bg-indigo-500' : 'bg-indigo-100'"
            class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
            <span class="sr-only">Use setting</span>
            <span aria-hidden="true" :class="value ? 'translate-x-6' : 'translate-x-0'"
                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
        </Switch>
    </div>
</template>
  

  
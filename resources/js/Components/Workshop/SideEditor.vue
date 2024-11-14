<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { Component } from 'vue'

import Accordion from 'primevue/accordion'
import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'

import EditorAndPanelProperties from "@/Components/CMS/Fields/EditorAndPanelProperties.vue"
import ButtonVisibleLoggedIn from '@/Components/CMS/Fields/ButtonVisibleLoggedIn.vue'
import PanelProperties from '@/Components/CMS/Fields/PanelProperties.vue'
/* import SideEditorText from '@/Components/Workshop/SideEditor/SideEditorText.vue' */
import SideEditorInputHTML from '@/Components/Workshop/SideEditor/SideEditorInputHTML.vue'
import Border from '@/Components/CMS/Fields/Border.vue'
import Padding from '@/Components/CMS/Fields/Padding.vue'
import Margin from '@/Components/CMS/Fields/Margin.vue'
import Dimension from '@/Components/CMS/Fields/Dimension.vue'
import Link from '@/Components/CMS/Fields/Link.vue'
import Background from '@/Components/CMS/Fields/Background.vue'
import ButtonProperties from '@/Components/CMS/Fields/ButtonProperties.vue'
import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/CMS/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorForm.vue"
import socialMedia from '@/Components/CMS/Fields/SocialMedia.vue'
import Script from '@/Components/CMS/Fields/Script.vue'
import SelectLayout from '@/Components/CMS/Fields/SelectLayout.vue'
import InputText from 'primevue/inputtext'
import { isArray, set as setLodash, get, cloneDeep } from 'lodash'
import { routeType } from '@/types/route'
import Icon from '@/Components/Icon.vue';
import OverviewForm from '@/Components/CMS/Fields/OverviewForm.vue'
import ArrayPhone from '@/Components/CMS/Fields/ArrayPhone.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faRectangleWide } from '@fal'
import { faDotCircle } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faRectangleWide, faDotCircle)

const props = defineProps<{
    blueprint: Array
    uploadImageRoute?: routeType
}>()

const modelValue = defineModel()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const openPanel = ref(0)

// Component side editor
const getComponent = (componentName: string) => {
    const components: Component = {
        'text': InputText,
        'editorhtml': SideEditorInputHTML,
        'upload_image': UploadImage,
        'payment_templates': Payments,
        'editor': Editor,
        'socialMedia': socialMedia,
        "EditorAndPanelProperties": EditorAndPanelProperties,
        "VisibleLoggedIn": ButtonVisibleLoggedIn,
        "properties": PanelProperties,
        "background": Background,
        "border": Border,
        "padding": Padding,
        "margin": Margin,
        "dimension": Dimension,
        "button": ButtonProperties,
        "link": Link,
        "overview_form": OverviewForm,
        "layout_type": SelectLayout,
        "script": Script,
        "arrayPhone":ArrayPhone
    }

    return components[componentName]
}

const onUpdateValue = () => {
    emits('update:modelValue', modelValue.value)
}

// Helper function to get nested value using path array
const getFormValue = (data: any, fieldKeys: string | string[]) => {
    const keys = Array.isArray(fieldKeys) ? fieldKeys : [fieldKeys];
    return keys.reduce((acc, key) => acc && acc[key], data) ?? null;
};

// Helper function to set nested value using path array
const setFormValue = (mValue: any, fieldKeys: string | string[], newVal: any) => {
    const keys = Array.isArray(fieldKeys) ? fieldKeys : [fieldKeys];
    setLodash(mValue, keys, newVal);
    onUpdateValue();
};

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
            setLodash(data, keyPath, null);
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
    emits('update:modelValue', setFormValues(props.blueprint, cloneDeep(props.modelValue)));
});


</script>

<template>
   <Accordion v-model:value="openPanel" class="w-full">
        <AccordionPanel 
            v-for="(field, index) of blueprint.filter((item)=>item.type != 'hidden')" 
            :key="index" 
            :value="index"
        >
            <AccordionHeader>
                <div>
                    <Icon v-if="field?.icon" :data="field.icon" />
                    {{ field.name }}
                </div>
            </AccordionHeader>

            <AccordionContent class="px-0 py-2">
                <div class="bg-white mt-[0px]">
                    <template v-if="field.replaceForm">
                        <div v-for="form in field.replaceForm">
                            <div v-if="form.type != 'hidden'">
                                <div class="my-2 text-xs font-semibold">{{ get(form,'label','') }}</div>
                                <component :is="getComponent(form.type)" :key="form.key"
                                    :modelValue="getFormValue(modelValue, [...field.key, ...get(form,'key',[])])"
                                    @update:modelValue="newValue => setFormValue(modelValue, [...field.key, ...get(form,'key',[])], newValue)"
                                    :uploadRoutes="uploadImageRoute" v-bind="{ ...form?.props_data, background }" />
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="my-2 text-xs font-semibold">{{ get(field,'label','') }}</div>
                        <component :is="getComponent(field.type)" :key="field.key"
                            :modelValue="getFormValue(modelValue, field.key)"
                            @update:modelValue="newValue => setFormValue(modelValue, field.key, newValue)"
                            :uploadRoutes="uploadImageRoute" v-bind="{ ...form?.props_data, background }" />
                    </template>
                </div>
            </AccordionContent>
        </AccordionPanel>
    </Accordion>
</template>


<style lang="scss" scoped>
.editor-content {
    background-color: white;
    border: solid;
}

.p-inputtext {
    width: 100%;
}

:deep(.p-accordionpanel.p-accordionpanel-active > .p-accordionheader) {
    background-color: #E2E8F0 !important;
    border-radius: 0 !important;
}

:deep(.p-accordionpanel.p-accordionpanel-active > .p-accordionheader:hover) {
    background-color: #E2E8F0 !important;
    /* color: #E2E8F0 !important; */
    border-radius: 0 !important;
}

</style>

<script setup lang="ts">
import { ref } from 'vue'
import type { Component } from 'vue'

import Accordion from 'primevue/accordion'
import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'

import EditorAndPanelProperties from "@/Components/Websites/Fields/EditorAndPanelProperties.vue"
import ButtonVisibleLoggedIn from '@/Components/Websites/Fields/ButtonVisibleLoggedIn.vue'
import PanelProperties from '@/Components/Websites/Fields/PanelProperties.vue'
import SideEditorText from '@/Components/Websites/SideEditor/SideEditorText.vue'
import SideEditorInputHTML from '@/Components/Websites/SideEditor/SideEditorInputHTML.vue'
import Border from '@/Components/Websites/Fields/Border.vue'
import Padding from '@/Components/Websites/Fields/Padding.vue'
import Margin from '@/Components/Websites/Fields/Margin.vue'
import Dimension from '@/Components/Websites/Fields/Dimension.vue'
import Link from '@/Components/Websites/Fields/Link.vue'
import Background from '@/Components/Websites/Fields/Background.vue'
import ButtonProperties from '@/Components/Websites/Fields/ButtonProperties.vue'
import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorForm.vue"
import socialMedia from '@/Components/Websites/Fields/SocialMedia.vue'
import FooterColumn from '@/Components/Websites/Fields/FooterColumn.vue'
import { isArray, set as setLodash } from 'lodash'
import { routeType } from '@/types/route'
import BorderProperty from './Fields/Properties/BorderProperty.vue'
import PaddingMarginProperty from './Fields/Properties/PaddingMarginProperty.vue'
import Icon from '@/Components/Icon.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faRectangleWide } from '@fal'
import { faDotCircle } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faRectangleWide, faDotCircle)


const props = defineProps<{
    bluprint: Array
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
        'text': SideEditorText,
        'editorhtml': SideEditorInputHTML,
        'upload_image': UploadImage,
        'payment_templates': Payments,
        'editor': Editor,
        'socialMedia': socialMedia,
        'footerColumn': FooterColumn,
        "EditorAndPanelProperties": EditorAndPanelProperties,
        "VisibleLoggedIn": ButtonVisibleLoggedIn,
        "properties" : PanelProperties,
        "background" : Background,
        "border" : Border,
        "padding" : Padding,
        "margin" : Margin,
        "dimension" : Dimension,
        "button" : ButtonProperties,
        "link" : Link
    }

    return components[componentName]
}

const onUpdateValue = () => {
    emits('update:modelValue', modelValue.value)
}

// To trick the modelValue with deep object (['container', 'properties'])
const getFormValue = (data: Object, fieldKeys: string | string[]) => {
    if (Array.isArray(fieldKeys)) {
        return fieldKeys.reduce((acc, key) => {
            if (acc && typeof acc === "object" && key in acc) return acc[key]
            return null
        }, data)
    } else {
        return data[fieldKeys]
    }
}
const setFormValue = (mValue: Object, fieldKeys: string | string[], newVal) => {
    setLodash(modelValue.value, fieldKeys, newVal)
    onUpdateValue()
}


</script>

<template>
    <Accordion>
        <AccordionPanel v-for="(field, index) in bluprint" :key="index" :value="index" @click="openPanel = index">
            <AccordionHeader>
                <div>
                    <Icon :data="field.icon" />
                    {{ field.name }}
                </div>
            </AccordionHeader>

            <AccordionContent class="px-0 py-2">
                <!-- Component side editor -->
                <div class="bg-white mt-[0px]">
                    <!-- field key: {{ field.key }} -->
                    <!-- model value: <pre>{{ modelValue }}</pre> -->
                    <!-- {{ modelValue[field.key] }} -->
                    <!-- <pre>{{ modelValue }}</pre> -->

                    <!-- If field have 'replaceform' and in [] -->
                    <template v-if="field.replaceForm">
                        <template v-for="form in field.replaceForm">
                            <!-- If multi type -->
                            <template v-if="isArray(form.type)">
                                <component
                                    v-for="(type, indexType) in form.type"
                                    :is="getComponent(type)"
                                    :modelValue="getFormValue(modelValue, form.key)"
                                    @update:modelValue="newValue => setFormValue(modelValue, form.key, newValue)"
                                    :uploadRoutes="uploadImageRoute"
                                    v-bind="field?.props_data"
                                />
                            </template>

                            <template v-else>
                            <!-- If single type -->
                                <component
                                    :is="getComponent(form.type)"
                                    :key="form.key"
                                    :modelValue="getFormValue(modelValue, form.key)"
                                    @update:modelValue="newValue => setFormValue(modelValue, form.key, newValue)"
                                    :uploadRoutes="uploadImageRoute"
                                    v-bind="field?.props_data"
                                />
                            </template>
                        </template>
                    </template>
                    
                    <!-- If have no 'replaceform' -->
                    <template v-else>
                        <template v-if="isArray(field.type)">
                            <!-- If multi type -->
                            <component
                                v-for="(type, indexType) in field.type"
                                :is="getComponent(type)"
                                :modelValue="getFormValue(modelValue, field.key)"
                                @update:modelValue="newValue => setFormValue(modelValue, field.key, newValue)"
                                :uploadRoutes="uploadImageRoute"
                                v-bind="field?.props_data"
                            />
                        </template>
    
                        <template v-else>
                            <!-- If single type -->
                            <component
                                :is="getComponent(field.type)"
                                :key="field.key"
                                :modelValue="getFormValue(modelValue, field.key)"
                                @update:modelValue="newValue => setFormValue(modelValue, field.key, newValue)"
                                :uploadRoutes="uploadImageRoute"
                                v-bind="field?.props_data"
                            />
                        </template>
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
    width: 100%
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

/* :deep(.p-accordioncontent-content) {
    padding: 0px;
} */


/* :deep(.p-accordionpanel:not(.p-disabled).p-accordionpanel-active > .p-accordionheader .p-accordionheader-toggle-icon) {
    color: #E2E8F0 !important;
} */
</style>

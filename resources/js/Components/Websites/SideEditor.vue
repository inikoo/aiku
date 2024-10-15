<script setup lang="ts">
import { ref } from 'vue'

import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import InputText from 'primevue/inputtext';
import { trans } from 'laravel-vue-i18n'
import SelectButton from 'primevue/selectbutton';
import EditorAndPanelProperties from "@/Components/Websites/Fields/EditorAndPanelProperties.vue"
import ButtonVisibleLoggedIn from '@/Components/Websites/Fields/ButtonVisibleLoggedIn.vue';
import PanelProperties from '@/Components/Websites/Fields/PanelProperties.vue';

import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorForm.vue"
import socialMedia from '@/Components/Websites/Fields/SocialMedia.vue'
import FooterColumn from '@/Components/Websites/Fields/FooterColumn.vue';
import { isArray } from 'lodash';


const props = defineProps<{
    modelValue: any,
    bluprint: Array
    uploadImageRoute?: routeType
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const openPanel = ref(0)

const getComponent = (componentName: string) => {
    const components: Component = {
        'text': InputText,
        'upload_image': UploadImage,
        'payment_templates': Payments,
        'editor': Editor,
        'socialMedia': socialMedia,
        'footerColumn': FooterColumn,
        "EditorAndPanelProperties": EditorAndPanelProperties,
        "VisibleLoggedIn": ButtonVisibleLoggedIn,
        "properties" : PanelProperties
    }

    return components[componentName]
}

const visible = ref("all")

const options = ref([
    { label: 'All', value: 'all' },
    { label: 'Logged In', value: 'login' },
    { label: 'Logged Out', value: 'logout' },
]);
const onUpdateValue = (field, value) => {
    emits('update:modelValue', {
        ...props.modelValue, [field.key]: value
    })
}


</script>

<template>
    <Accordion>
        <AccordionPanel v-for="(field, index) in bluprint" :key="index" :value="index" @click="openPanel = index">
            <AccordionHeader>
                {{ field.name }}
            </AccordionHeader>
            <AccordionContent>
                <div class="bg-white mt-[0px] py-4">
                    <template v-if="isArray(field.type)" v-for="(type, indexType) in field.type">
                        <component  :is="getComponent(type)"
                            v-model="modelValue[field.key]" @update:modelValue="value => onUpdateValue(field, value)"
                            :uploadRoutes="uploadImageRoute" v-bind="field?.props_data" />
                    </template>
                    <component v-else :is="getComponent(field.type)" :key="field.key" v-model="modelValue[field.key]"
                        @update:modelValue="value => onUpdateValue(field, value)" :uploadRoutes="uploadImageRoute"
                        v-bind="field?.props_data" />
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

/* :deep(.p-accordionpanel:not(.p-disabled).p-accordionpanel-active > .p-accordionheader .p-accordionheader-toggle-icon) {
    color: #E2E8F0 !important;
} */
</style>

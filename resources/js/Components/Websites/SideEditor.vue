<script setup lang="ts">
import { ref } from 'vue'

import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import InputText from 'primevue/inputtext';

import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorForm.vue"
import socialMedia from '@/Components/Websites/Fields/SocialMedia.vue'
import FooterColumn from '@/Components/Websites/Fields/FooterColumn.vue';


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
        'footerColumn': FooterColumn
    }

    return components[componentName]
}

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
                <component :is="getComponent(field.type)" :key="field.key" v-model="modelValue[field.key]"
                    @update:modelValue="value => onUpdateValue(field, value)" :uploadRoutes="uploadImageRoute"
                    v-bind="field?.props_data" />
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
    background-color: #800080 !important;
    /* Ungu */
    color: white !important;
    /* Warna teks */
    margin-bottom: 12px !important;
    border-radius: 0 !important;
}

:deep(.p-accordionpanel.p-accordionpanel-active > .p-accordionheader:hover) {
    background-color: #800080 !important;
    /* Ungu saat hover */
    color: white !important;
    border-radius: 0 !important;
}

:deep(.p-accordionpanel:not(.p-disabled).p-accordionpanel-active > .p-accordionheader .p-accordionheader-toggle-icon) {
    color: white !important;
    /* Warna teks */
}
</style>

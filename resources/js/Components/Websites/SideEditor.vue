<script setup lang="ts">
import type { Component } from 'vue'

import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import socialMedia from '@/Components/Websites/Fields/SocialMedia.vue'


const props = defineProps<{
    modelValue: any,
    bluprint: Array
    uploadImageRoute?:routeType
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const getComponent = (componentName: string) => {
    const components: Component = {
        'text': PureInput,
        'upload_image' : UploadImage,
        'payment_templates' : Payments,
        'editor' : Editor,
        'socialMedia' : socialMedia
    }

    return components[componentName]
}

const onUpdateValue = (field,value) => {
    emits('update:modelValue', {
        ...props.modelValue, [field.key] : value
    })
}


</script>

<template>
      <!--   <div v-for="field in bluprint" :key="field.key" class="mx-auto w-full max-w-md ">
            <Disclosure v-slot="{ open }">
                <DisclosureButton class="flex w-full justify-between px-4 py-4 text-left text-sm font-medium border-b border-white bg-[#D1D5DB]" >
                    <span class="font-medium text-sm font-bold">{{ field.name }}</span>
                </DisclosureButton>
                <DisclosurePanel class="px-4 pb-2 pt-4 text-sm text-gray-500 bg-[#D1D5DB]">
                    <section class="w-full">
                        <component
                            :is="getComponent(field.type)"
                            :key="field.key"
                            v-model="modelValue[field.key]"
                            @update:modelValue="value => onUpdateValue(field, value)"
                            :uploadRoutes="uploadImageRoute"
                            v-bind="field?.props_data"
                        />
                    </section>
                </DisclosurePanel>
            </Disclosure>
        </div> -->

        <Accordion v-for="(field,index) in bluprint" :key="index" >
            <AccordionPanel :value="index">
                <AccordionHeader>{{ field.name }}</AccordionHeader>
                <AccordionContent>
                    <component
                            :is="getComponent(field.type)"
                            :key="field.key"
                            v-model="modelValue[field.key]"
                            @update:modelValue="value => onUpdateValue(field, value)"
                            :uploadRoutes="uploadImageRoute"
                            v-bind="field?.props_data"
                        />
                </AccordionContent>
            </AccordionPanel>
        </Accordion>
</template>


<style scss scoped>
.editor-content{
    background-color: white;
    border: solid;
}
</style>

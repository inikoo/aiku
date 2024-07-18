<script setup lang="ts">
import type { Component } from 'vue'

import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import socialMedia from '@/Components/Websites/Fields/SocialMedia.vue'

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)


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
    <div class="p-4 mb-3">
        <div v-for="field in bluprint" :key="field.key" class="mx-auto w-full max-w-md rounded-2xl bg-white mb-2">
            <Disclosure v-slot="{ open }">
                <DisclosureButton
                    class="flex w-full justify-between rounded-lg px-4 py-2 text-left text-sm font-medium text-purple-900 hover:bg-purple-200 focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75"
                >
                    <span class="font-medium text-sm">{{ field.name }}</span>
                </DisclosureButton>
                <DisclosurePanel class="px-4 pb-2 pt-4 text-sm text-gray-500">
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
        </div>
    </div>
</template>


<style scss scoped>
.editor-content{
    background-color: white;
    border: solid;
}


</style>

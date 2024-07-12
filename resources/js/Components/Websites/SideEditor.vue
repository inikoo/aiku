<script setup lang="ts">
import type { Component } from 'vue'


import PureInput from '@/Components/Pure/PureInput.vue'
import UploadImage from '@/Components/Pure/UploadImage.vue'
import Payments from '@/Components/Websites/Fields/Payment.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)


const props = defineProps<{
    modelValue: any,
    bluprint: Array
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const getComponent = (componentName: string) => {
    const components: Component = {
        'text': PureInput,
        'upload_image' : UploadImage,
        'payment_templates' : Payments
        
    }

    return components[componentName]
}

const onUpdateValue = (field,value) => {
    emits('update:modelValue', {
        ...props.modelValue, [field.key] : value
    })
}

console.log('aa', props)

</script>

<template>
    <div class="p-4 mb-3">

        <section v-for="field in bluprint" :key="field.key" class="mb-3">
            <div class="font-medium text-sm mb-2">{{ field.name }}</div>
            <section class="w-full">
                <component :is="getComponent(field.type)" v-model="modelValue[field.key]" @update:modelValue="value => onUpdateValue(field,value)"/>
            </section>
        </section>
    </div>
</template>


<style scss></style>

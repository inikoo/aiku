
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { ref, watch } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Footer from '@/Components/CMS/Footer/index.vue'
import { faHandPointer, faHandRock, faPlus } from '@fas';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
import HyperlinkTools from '@/Components/CMS/Fields/Hyperlinktools.vue'
import { get } from 'lodash'
import HyperInfoTools from '@/Components/CMS/Fields/InfoFieldTools.vue'
import SocialMediaPicker from "@/Components/CMS/Fields/SocialMediaTools.vue"
import Descriptor from './Descriptor'
import Tools from './Tools.vue'
import { useForm } from '@inertiajs/vue3';

library.add(faHandPointer, faHandRock, faPlus)

//InitialData
const ToolsBluprint = Descriptor.footerDataTools
const ColumnsBluprint = Descriptor.footerDataLayout.initialColumns
const copyRight = Descriptor.footerDataLayout.copyRight
const socials = Descriptor.footerDataLayout.socials
const toolsValue = useForm({
    theme : 'light-theme',
    hand  : 'click',
    columnType : null
})
const footerDataLayout = useForm({...Descriptor.footerDataLayout})

// selectedData
const selectedTheme = ref(ToolsBluprint.theme[0])
const columnsTypeTheme = ref(ToolsBluprint.columnsType[0])
const tool = ref(ToolsBluprint.hand[0])
const columnSelected = ref(ColumnsBluprint[0]);





</script>

<template>
    <div class="bg-white">
        <div class="p-1 border border-gray-300 overflow-y-auto overflow-x-hidden">
            <Tools 
              :toolsBluprint="ToolsBluprint" 
              v-model="toolsValue"
            />
        </div>
        <div class="flex justify-center items-center bg-gray-200 border border-gray-300 transform scale-80 w-full">
            <Footer 
              :tools="toolsValue"
              :footerDataLayout="footerDataLayout"
            />
        </div>
        </div>
</template>


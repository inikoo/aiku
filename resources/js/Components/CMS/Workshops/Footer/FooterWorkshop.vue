
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import Footer from '@/Components/CMS/Footer/index.vue'
import { menuTypeList,  menuTypeDescription, menuTypeInfo, footerDataLayout, footerDataTools } from './Descriptor'
import Tools from './Tools.vue'
import { useForm } from '@inertiajs/vue3';


//InitialData
const ToolsBluprint = footerDataTools
const toolsValue = useForm({
    theme : 'light-theme',
    hand  : 'click',
    columnType : null
})
const footerDataLayoutForm = useForm({...footerDataLayout})

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
              :footerDataLayout="footerDataLayoutForm"
            />
        </div>
        </div>
</template>


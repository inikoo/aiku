<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import { ref, toRaw, inject } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { trans } from "laravel-vue-i18n";
import { set } from "lodash";
import { routeType } from "@/types/route"
import EditorV2 from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"

library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
    modelValue: any
    webpageData?: any
    blockData: Object
    // uploadRoutes: routeType
}>()

const emits = defineEmits<{
    (e: "update:modelValue", value: any): void
    (e: "autoSave"): void
}>()


const getColumnWidthClass = (layoutType: string, index: number) => {
    switch (layoutType) {
        case "12":
            return index === 0 ? " sm:w-1/2 md:w-1/3" : " sm:w-1/2 md:w-2/3"
        case "21":
            return index === 0 ? " sm:w-1/2 md:w-2/3" : " sm:w-1/2 md:w-1/3"
        case "13":
            return index === 0 ? " md:w-1/4" : " md:w-3/4"
        case "31":
            return index === 0 ? " sm:w-1/2 md:w-3/4" : " sm:w-1/2 md:w-1/4"
        case "211":
            return index === 0 ? " md:w-1/2" : " md:w-1/4"
        case "2":
            return index === 0 ? " md:w-1/2" : " md:w-1/2"
        case "3":
            return index === 0 ? " md:w-1/3" : " md:w-1/3"
        case "4":
            return index === 0 ? " md:w-1/4" : " md:w-1/4"
        default:
            return "w-full"
    }
}

const getImageSlots = (layoutType: string) => {
    switch (layoutType) {
        case "4":
            return 4
        case "3":
        case "211":
            return 3
        case "2":
        case "12":
        case "21":
        case "13":
        case "31":
            return 2
        default:
            return 1
    }
}
</script>

<template>
    <div class="flex flex-wrap">
        <div v-for="index in getImageSlots(modelValue?.value?.layout_type)"
            :key="`${index}-${modelValue?.value?.images?.[index - 1]?.source?.avif}`"
            class="group relative p-2 hover:bg-white/40"
            :class="getColumnWidthClass(modelValue?.value?.layout_type, index - 1)">
            <div v-html="modelValue[index]"></div>
        </div>
    </div>


</template>

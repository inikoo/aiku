<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { computed, ref } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import Tabs from "@/Components/Navigation/Tabs.vue"
import LayoutWorkshop from "@/Components/Websites/Layout/LayoutWorkshop.vue"
import WorkshopProduct from "@/Components/Websites/Product/ProductWorkshop.vue"
import { capitalize } from "@/Composables/capitalize"
import CategoryWorkshop from '@/Components/Websites/Category/CategoryWorkshop.vue'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: {}
    imagesUploadRoute: routeType
    updateRoute: routeType
    emailTemplate: routeType
    publishRoute: routeType
    loadRoute: routeType
}>()


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <div>sdfsdf</div>
</template>

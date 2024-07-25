<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Unlayer from "@/Components/Websites/Outboxes/Unlayer.vue"

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


const dummyRoute = {
    name : '',
    parameters : ""
}

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Unlayer :updateRoute="dummyRoute" :loadRoute="dummyRoute" :imagesUploadRoute="dummyRoute" :mailshot="{}"/>
</template>

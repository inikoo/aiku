<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { computed, ref } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import Tabs from "@/Components/Navigation/Tabs.vue"
import WorkshopHeader from "@/Components/CMS/Workshops/HeaderWorkshop.vue"
import WorkshopMenu from "@/Components/CMS/Workshops/Menu/MenuWorkshop.vue"
import LayoutWorkshop from "@/Components/CMS/Workshops/LayoutWorkshop.vue"
import WorkshopFooter from "@/Components/CMS/Workshops/Footer/FooterWorkshop.vue"
import WorkshopProduct from "@/Components/CMS/Workshops/ProductWorkshop.vue"
import ColorSchemeWorkshop from "@/Components/CMS/Workshops/ColorSchemeWorkshop.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLayoutStore } from "@/Stores/layout"

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: {}
    tabs: {
        current: string
        navigation: {}
    }
    color_scheme?: {}
    header?: {}
    menu?: {}
    footer?: {}
    category?: {}
    product?: {}
    website_layout: {}
}>()

let currentTab = ref(props.tabs?.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

    const components = {
        color_scheme: ColorSchemeWorkshop,
        header: WorkshopHeader,
        menu: WorkshopMenu,
        footer: WorkshopFooter,
        website_layout: LayoutWorkshop,
        product: WorkshopProduct
    }
    return components[currentTab.value]
})

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" />
</template>

import type { Component } from 'vue'

import WowsbarBanner from '@/Components/Fulfilment/Website/Block/WowsbarBanner.vue'
import ProductPage from '@/Components/Fulfilment/Website/Block/ProductPage.vue'
import Text from '@/Components/Fulfilment/Website/Block/TextContent.vue'
import FamilyPageOffer from '@/Components/Fulfilment/Website/Block/FamilyPage-offer.vue'
import ProductList from '@/Components/Fulfilment/Website/Block/ProductList.vue'
import CTA from '@/Components/Fulfilment/Website/Block/CTA.vue'
import Rewiews from '@/Components/Fulfilment/Website/Block/Reviews.vue'
import Image from '@/Components/Fulfilment/Website/Block/Image.vue'
import CTA2 from '@/Components/Fulfilment/Website/Block/CTA2.vue'
import Gallery from '@/Components/Fulfilment/Website/Block/Gallery.vue'
import Iframe from '@/Components/Fulfilment/Website/Block/Iframe.vue'
import Action from "@/Components/Forms/Fields/Action.vue"



export const getComponent = (componentName: string) => {
    const components: Component = {
        'bannerWowsbar': WowsbarBanner,
        'ProductPage': ProductPage,
        'text': Text,
        'FamilyPageOffer': FamilyPageOffer,
        'ProductList': ProductList,
        'CTA': CTA,
        'CTA2': CTA2,
        'Reviews': Rewiews,
        'Image': Image,
        'Gallery': Gallery,
        "Iframe": Iframe
    }

    return components[componentName] ?? null
}
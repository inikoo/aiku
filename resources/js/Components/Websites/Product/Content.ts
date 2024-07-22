import type { Component } from 'vue'

//Components
import Product1 from '@/Components/Websites/Product/ProductTemplates/Product1/Product1Edit.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
import { data as dataProduct1   } from '@/Components/Websites/Footer/FooterTemplates/Footer1/descriptor'

export const getComponent = (componentName: string) => {
    const components: Component = {
        'product1': Product1,
    }

    return components[componentName] ?? NotFoundComponents
}


export const getDescriptor = (componentName: string) => {
    const components: Component = {
        'product1': { data : dataProduct1 },
    }

    return components[componentName]
}
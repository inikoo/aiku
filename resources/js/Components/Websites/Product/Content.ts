import type { Component } from 'vue'

//Components
import Product1 from '@/Components/Websites/Product/ProductTemplates/Product1/Product1Edit.vue'
import Product2 from '@/Components/Websites/Product/ProductTemplates/Product2/Product2Edit.vue'
import Product3 from '@/Components/Websites/Product/ProductTemplates/product3/Product3Edit.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
import { data as dataProduct1 } from '@/Components/Websites/Footer/FooterTemplates/Footer1/descriptor'

export const getComponent = (componentName: string) => {
    const components: Component = {
        'product1': Product1,
        'product2': Product2,
        'Product3': Product3
    }

    return components[componentName] ?? NotFoundComponents
}


export const getDescriptor = (componentName: string) => {
    const components: Component = {
        'product1': { data: dataProduct1 },
        'product2': { data: dataProduct1 },
    }

    return components[componentName]
}
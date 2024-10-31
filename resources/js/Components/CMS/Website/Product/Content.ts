import type { Component } from 'vue'

//Components
import Product1 from '@/Components/CMS/Website/Product/ProductTemplates/Product1/Product1Edit.vue'
import Product2 from '@/Components/CMS/Website/Product/ProductTemplates/Product2/Product2Edit.vue'
import Product3 from '@/Components/CMS/Website/Product/ProductTemplates/Product2/Product2Edit.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'



export const getComponent = (componentName: string) => {
    const components: Component = {
        'product1': Product1,
        'product2': Product2,
        'product3': Product3
    }

    return components[componentName] ?? NotFoundComponents
}

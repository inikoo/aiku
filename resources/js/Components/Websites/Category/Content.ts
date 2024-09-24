import type { Component } from 'vue'

import Template1 from '@/Components/Websites/Category/Templates/Template1.vue'
import Template2 from '@/Components/Websites/Category/Templates/Template2.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'template1': Template1,
        'template2': Template2,
    }

    return components[componentName] || NotFoundComponents
}
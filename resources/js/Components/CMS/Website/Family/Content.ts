import type { Component } from 'vue'

import Template1 from '@/Components/CMS/Website/Family/Templates/Template1.vue'
import Template2 from '@/Components/CMS/Website/Family/Templates/Template2.vue'
import Template3 from '@/Components/CMS/Website/Family/Templates/Template3.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'template1': Template1,
        'template2': Template2,
        'template3': Template3,
    }

    return components[componentName] || NotFoundComponents
}
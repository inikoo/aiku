import type { Component } from 'vue'

//Components
import Footer1 from '@/Components/CMS/Website/Footers/Footer1Workshop.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1,
    }

    return components[componentName] ?? NotFoundComponents
}


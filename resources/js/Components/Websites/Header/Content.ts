import type { Component } from 'vue'

//Components
import Header1 from '@/Components/Websites/Header/HeaderTemplates/Header1/Header1.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'



//render componnets
import RenderHeader1 from '@/Components/Websites/Header/HeaderTemplates/Header1/RenderHeader1.vue'

export const getComponent = (componentName: string) => {
    const components: Component = {
        'header-1': Header1,
    }

    return components[componentName] || NotFoundComponents
}



export const getRenderComponent = (componentName: string) => {
    const components: Component = {
        'header-1': RenderHeader1,
    }

    return components[componentName] || NotFoundComponents
}
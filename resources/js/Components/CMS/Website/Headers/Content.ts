import type { Component } from 'vue'

//Components
import Header1 from '@/Components/CMS/Website/Headers/Header1Workshop.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'



//render componnets
import RenderHeader1 from '@/Components/CMS/Website/Headers/Header1Iris.vue'

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
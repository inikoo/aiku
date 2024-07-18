import type { Component } from 'vue'

//Components
import Header1 from '@/Components/Websites/Header/HeaderTemplates/Header1/Header1.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
import { data as dataHeader1, bluprintForm as bluprintForm1  } from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'

//render componnets
import RenderHeader1 from '@/Components/Websites/Header/HeaderTemplates/Header1/RenderHeader1.vue'

export const getComponent = (componentName: string) => {
    const components: Component = {
        'header1': Header1,
    }

    return components[componentName] || NotFoundComponents
}


export const getDescriptor = (componentName: string) => {
    const components: Component = {
        'header1': { data : dataHeader1, bluprint : bluprintForm1},
    }

    return components[componentName]
}



export const getRenderComponent = (componentName: string) => {
    const components: Component = {
        'header1': RenderHeader1,
    }

    return components[componentName] || NotFoundComponents
}
import type { Component } from 'vue'

//Components
import Footer1 from '@/Components/Websites/Footer/FooterTemplates/Footer1/Footer1.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
import { data as dataHeader1, bluprintForm as bluprintForm1  } from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'

export const getComponent = (componentName: string) => {
    const components: Component = {
        'footer1': Footer1,
    }

    return components[componentName] ?? NotFoundComponents
}


export const getDescriptor = (componentName: string) => {
    const components: Component = {
        'footer1': { data : dataHeader1, bluprint : bluprintForm1},
    }

    return components[componentName]
}
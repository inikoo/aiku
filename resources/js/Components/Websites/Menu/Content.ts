import type { Component } from 'vue'

//Components
import Menu1 from '@/Components/Websites/Menu/Menu1/Menu1.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'menu1': Menu1,
    }

    return components[componentName] || NotFoundComponents
}



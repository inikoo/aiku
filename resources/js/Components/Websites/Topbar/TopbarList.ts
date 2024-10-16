/**
 *  author: Vika Aqordi
 *  created on: 14-10-2024
 *  github: https://github.com/aqordeon
 *  copyright: 2024
*/

import type { Component } from 'vue'

//Components
import Topbar1 from '@/Components/Websites/Topbar/Template/Topbar1.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
// import { data as dataHeader1, bluprintForm as bluprintForm1  } from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'

//render componnets
// import RenderHeader1 from '@/Components/Websites/Header/HeaderTemplates/Header1/RenderHeader1.vue'

export const getTopbarComponent = (componentName: string) => {
    const components: Component = {
        'topbar_1': Topbar1,
    }

    return components[componentName] || NotFoundComponents
}


// export const getDescriptor = (componentName: string) => {
//     const components: Component = {
//         'header1': { data : dataHeader1, bluprint : bluprintForm1},
//     }

//     return components[componentName]
// }



// export const getRenderComponent = (componentName: string) => {
//     const components: Component = {
//         'header1': RenderHeader1,
//     }

//     return components[componentName] || NotFoundComponents
// }
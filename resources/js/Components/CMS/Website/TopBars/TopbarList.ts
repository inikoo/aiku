/**
 *  author: Vika Aqordi
 *  created on: 14-10-2024
 *  github: https://github.com/aqordeon
 *  copyright: 2024
*/

import type { Component } from 'vue'

//Components
import Topbar1 from '@/Components/CMS/Website/TopBars/Template/Topbar1.vue'
import Topbar2 from '@/Components/CMS/Website/TopBars/Template/Topbar2.vue'
import Topbar3 from '@/Components/CMS/Website/TopBars/Template/Topbar3.vue'
import NotFoundComponents from '@/Components/Fulfilment/Website/Block/NotFoundComponent.vue'


//descriptor
// import { data as dataHeader1, bluprintForm as bluprintForm1  } from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'

//render componnets
// import RenderHeader1 from '@/Components/Websites/Header/HeaderTemplates/Header1/Header1Iris.vue'

export const getTopbarComponent = (componentName: string) => {
    const components: Component = {
        'top-bar-1': Topbar1,
        'top-bar-2': Topbar2,
        'top-bar-3': Topbar3,
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
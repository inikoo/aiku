/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 30 Oct 2024 16:00:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import type { Component } from 'vue'

//Components
import Menu1 from '@/Components/CMS/Website/Menus/Menu1Workshop.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'menu-1': Menu1,
    }

    return components[componentName] || NotFoundComponents
}



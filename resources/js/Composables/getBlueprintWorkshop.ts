import type { Component } from 'vue'

import Footer1Blueprint from '@/Components/CMS/Website/Footers/footerTheme1/bluprint'

export const getBlueprint = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1Blueprint.blueprint,
    }
    return components[componentName] ?? []
}

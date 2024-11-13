import type { Component } from 'vue'

import Footer1Blueprint from '@/Components/CMS/Website/Footers/footerTheme1/bluprint'
import Topbar1Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar1/Blueprint"
import Topbar2Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar2/Blueprint"
import Topbar3Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar3/Blueprint"
import Header1Blueprint from'@/Components/CMS/Website/Headers/Header1/Blueprint'

export const getBlueprint = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1Blueprint.blueprint,
        'top-bar-1': Topbar1Blueprint.blueprint,
        'top-bar-2': Topbar2Blueprint.blueprint,
        'top-bar-3': Topbar3Blueprint.blueprint,
        'header-1' : Header1Blueprint.blueprint
    }
    return components[componentName] ?? []
}

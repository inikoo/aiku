import type { Component } from 'vue'
import { defineAsyncComponent } from 'vue'

const components: Record<string, Component> = {
    'footer-1': defineAsyncComponent(() => import('@/Components/CMS/Website/Footers/footerTheme1/Footer1Iris.vue')),
    'header-1': defineAsyncComponent(() => import('@/Components/CMS/Website/Headers/Header1/Header1Iris.vue')),
    'header-2': defineAsyncComponent(() => import('@/Components/CMS/Website/Headers/Header2/Header2Iris.vue')),
    'top-bar-1': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar1/Topbar1Iris.vue')),
    'top-bar-2': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar2/Topbar2Iris.vue')),
    'top-bar-3': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar3/Topbar3Iris.vue')),
    'menu-1': defineAsyncComponent(() => import('@/Components/CMS/Website/Menus/Menu1Workshop.vue')),
    'banner': defineAsyncComponent(() => import('@/Components/CMS/Webpage/WowsbarBanner/WowsbarBannerIris.vue')),
    'bento-grid-1': defineAsyncComponent(() => import('@/Components/CMS/Webpage/BentoGrid/BentoGridIris.vue')),
    'bricks': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Gallery/GalleryIris.vue')),
    'cta1': defineAsyncComponent(() => import('@/Components/CMS/Webpage/CTA/CTAIris.vue')),
    'cta2': defineAsyncComponent(() => import('@/Components/CMS/Webpage/CTA2/CTA2Iris.vue')),
    'cta3': defineAsyncComponent(() => import('@/Components/CMS/Webpage/CTA3/CTA3Iris.vue')),
    'department': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Department/DepartmentIris.vue')),
    'iframe': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Iframe/IframeIris.vue')),
    'images': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Image/ImageIris.vue')),
    'overview_aurora': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Overview/OverviewIris.vue')),
    'script': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Script/ScriptIris.vue')),
    'text': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Text/TextContentIris.vue')),
    'cta_aurora_1': defineAsyncComponent(() => import('@/Components/CMS/Webpage/CTAAurora1/CtaAurora1Iris.vue')),
    'overview_2': defineAsyncComponent(() => import('@/Components/CMS/Webpage/Overview2/Overview2Iris.vue')),
    'text-column': defineAsyncComponent(() => import('@/Components/CMS/Webpage/TextColumn/TextColumnIris.vue')),
    'top-bar-1-fulfilment': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar1Fulfilment/Topbar1FulfilmentIris.vue')),
    'top-bar-2-fulfilment': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar2Fulfilment/Topbar2FulfilmentIris.vue')),
    'top-bar-3-fulfilment': defineAsyncComponent(() => import('@/Components/CMS/Website/TopBars/Template/Topbar3Fulfilment/Topbar3FulfilmentIris.vue')),
}

const NotFoundComponent = defineAsyncComponent(() => import('@/Components/CMS/Webpage/NotFoundComponent.vue'))

export const getIrisComponent = (componentName: string) => {
    return components[componentName] ?? NotFoundComponent
}

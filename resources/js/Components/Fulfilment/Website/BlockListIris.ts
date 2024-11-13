import type { Component } from 'vue'

import WowsbarBanner from '@/Components/CMS/Webpage/WowsbarBanner/WowsbarBannerIris.vue'
import Text from '@/Components/CMS/Webpage/Text/TextContentIris.vue'
import CTA from '@/Components/CMS/Webpage/CTA/CTAIris.vue'
import Image from '@/Components/CMS/Webpage/Image/ImageIris.vue'
import CTA2 from '@/Components/CMS/Webpage/CTA2/CTA2Iris.vue'
import CTA3 from '@/Components/CMS/Webpage/CTA3/CTA3Iris.vue'
import Gallery from '@/Components/CMS/Webpage/Gallery/GalleryIris.vue'
import Iframe from '@/Components/CMS/Webpage/Iframe/IframeIris.vue'
import BentoGrid from '@/Components/CMS/Webpage/BentoGrid/BentoGridIris.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'
import Department from '@/Components/CMS/Webpage/Department/DepartmentIris.vue'
import Overview from '@/Components/CMS/Webpage/Overview/OverviewIris.vue'
import Script from '@/Components/CMS/Webpage/Script/ScriptIris.vue'
import CtaAurora1 from "@/Components/CMS/Webpage/CTAAurora1/CtaAurora1Iris.vue"
import Overview2 from "@/Components/CMS/Webpage/Overview2/Overview2Iris.vue"

export const getComponent = (componentName: string) => {
    const components: Component = {
        'banner': WowsbarBanner,
        "bento-grid-1": BentoGrid,
        "bricks": Gallery,
        //categories
        'cta1': CTA,
        'cta2': CTA2,
        'cta3': CTA3,
        "department": Department,
        "iframe": Iframe,
        'images': Image,
        "overview_aurora": Overview,
/*         'products': ProductList, */
        "script": Script,
        'text': Text,
        'cta_aurora_1' : CtaAurora1,
        'overview_2' : Overview2
        /* "product": Product, */
    }

    return components[componentName] ?? NotFoundComponents
}
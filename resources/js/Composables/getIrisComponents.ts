import type { Component } from 'vue'

//Components
import WowsbarBanner from '@/Components/CMS/Webpage/WowsbarBanner/WowsbarBannerIris.vue'
import Text from '@/Components/CMS/Webpage/Text/TextContentIris.vue'
import CTA from '@/Components/CMS/Webpage/CTA/CTAIris.vue'
import Image from '@/Components/CMS/Webpage/Image/ImageIris.vue'
import CTA2 from '@/Components/CMS/Webpage/CTA2/CTA2Iris.vue'
import CTA3 from '@/Components/CMS/Webpage/CTA3/CTA3Iris.vue'
import Gallery from '@/Components/CMS/Webpage/Gallery/GalleryIris.vue'
import Iframe from '@/Components/CMS/Webpage/Iframe/IframeIris.vue'
import BentoGrid from '@/Components/CMS/Webpage/BentoGrid/BentoGridIris.vue'
import Department from '@/Components/CMS/Webpage/Department/DepartmentIris.vue'
import Overview from '@/Components/CMS/Webpage/Overview/OverviewIris.vue'
import Script from '@/Components/CMS/Webpage/Script/ScriptIris.vue'
import CtaAurora1 from "@/Components/CMS/Webpage/CTAAurora1/CtaAurora1Iris.vue"
import Overview2 from "@/Components/CMS/Webpage/Overview2/Overview2Iris.vue"
import Footer1 from '@/Components/CMS/Website/Footers/footerTheme1/Footer1Iris.vue'
import Topbar1 from '@/Components/CMS/Website/TopBars/Template/Topbar1/Topbar1.vue'
import Topbar2 from '@/Components/CMS/Website/TopBars/Template/Topbar2/Topbar2.vue'
import Topbar3 from '@/Components/CMS/Website/TopBars/Template/Topbar3/Topbar3.vue'
import Header1 from '@/Components/CMS/Website/Headers/Header1/Header1Iris.vue'
import Menu1 from '@/Components/CMS/Website/Menus/Menu1Workshop.vue'
import TextColumn from '@/Components/CMS/Webpage/TextColumn/TextColumnIris.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'


export const getIrisComponent = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1,
        'header-1': Header1,
        'top-bar-1': Topbar1,
        'top-bar-2': Topbar2,
        'top-bar-3': Topbar3,
        'menu-1': Menu1,
        'banner': WowsbarBanner,  // not used fieldValue yet
        "bento-grid-1": BentoGrid,
        "bricks": Gallery,  // not used fieldValue yet
        'cta1': CTA,
        'cta2': CTA2,
        'cta3': CTA3,
        "department": Department,
      /*   'family': FamilyPageOffer, */
        "iframe": Iframe,
        'images': Image,
        "overview_aurora": Overview,
     /*    'product': ProductPage,
        'products': ProductList, */
        "script": Script,
        'text': Text,
        'cta_aurora_1' : CtaAurora1,
        'overview_2' : Overview2,
        'text-column' : TextColumn
       
    }
    return components[componentName] ?? NotFoundComponents
}


import type { Component } from 'vue'

//Components
import WowsbarBanner from '@/Components/CMS/Webpage/WowsbarBannerWorkshop.vue'
import ProductPage from '@/Components/CMS/Webpage/ProductPage.vue'
import Text from '@/Components/CMS/Webpage/TextContentWorkshop.vue'
import FamilyPageOffer from '@/Components/CMS/Webpage/FamilyPage-offer.vue'
import ProductList from '@/Components/CMS/Webpage/ProductList.vue'
import CTA from '@/Components/CMS/Webpage/CTAWorkshop.vue'
import Image from '@/Components/CMS/Webpage/ImageWorkshop.vue'
import CTA2 from '@/Components/CMS/Webpage/CTA2Workshop.vue'
import CTA3 from '@/Components/CMS/Webpage/CTA3Workshop.vue'
import Gallery from '@/Components/CMS/Webpage/GalleryWorkshop.vue'
import Iframe from '@/Components/CMS/Webpage/IframeWorkshop.vue'
import BentoGrid from '@/Components/CMS/Webpage/BentoGridWorksop.vue'
import Department from '@/Components/CMS/Webpage/DepartmentWorkshop.vue'
import Overview from '@/Components/CMS/Webpage/OverviewWorkshop.vue'
import Script from '@/Components/CMS/Webpage/ScriptWorkShop.vue'
import  CtaAurora1 from "@/Components/CMS/Webpage/CtaAurora1Workshop.vue"
import Overview2 from "@/Components/CMS/Webpage/Overview2Workshop.vue"
import Footer1 from '@/Components/CMS/Website/Footers/footerTheme1/Footer1Workshop.vue'
import Topbar1 from '@/Components/CMS/Website/TopBars/Template/Topbar1/Topbar1.vue'
import Topbar2 from '@/Components/CMS/Website/TopBars/Template/Topbar2/Topbar2.vue'
import Topbar3 from '@/Components/CMS/Website/TopBars/Template/Topbar3/Topbar3.vue'
import Header1 from '@/Components/CMS/Website/Headers/Header1/Header1Workshop.vue'
import NotFoundComponents from '@/Components/CMS/Webpage/NotFoundComponent.vue'


export const getComponent = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1,
        'banner': WowsbarBanner,
        "bento-grid-1": BentoGrid,
        "bricks": Gallery,
        'cta1': CTA,
        'cta2': CTA2,
        'cta3': CTA3,
        "department": Department,
        'family': FamilyPageOffer,
        "iframe": Iframe,
        'images': Image,
        "overview_aurora": Overview,
        'product': ProductPage,
        'products': ProductList,
        "script": Script,
        'text': Text,
        'cta_aurora_1' : CtaAurora1,
        'overview_2' : Overview2,
        'top-bar-1': Topbar1,
        'top-bar-2': Topbar2,
        'top-bar-3': Topbar3,
        'header-1': Header1,
    }
    return components[componentName] ?? NotFoundComponents
}


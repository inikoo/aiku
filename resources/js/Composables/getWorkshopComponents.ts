import type { Component } from 'vue'

import WowsbarBanner from '@/Components/CMS/Webpage/WowsbarBanner/WowsbarBannerWorkshop.vue'
import ProductPage from '@/Components/CMS/Webpage/ProductPage.vue'
import Text from '@/Components/CMS/Webpage/Text/TextContentWorkshop.vue'
import FamilyPageOffer from '@/Components/CMS/Webpage/FamilyPage-offer.vue'
import ProductList from '@/Components/CMS/Webpage/ProductList.vue'
import CTA from '@/Components/CMS/Webpage/CTA/CTAWorkshop.vue'
import ImageWorkshop from '@/Components/CMS/Webpage/Image/ImageWorkshop.vue'
import CTA2 from '@/Components/CMS/Webpage/CTA2/CTA2Workshop.vue'
import CTA3 from '@/Components/CMS/Webpage/CTA3/CTA3Workshop.vue'
import Gallery from '@/Components/CMS/Webpage/Gallery/GalleryWorkshop.vue'
import Iframe from '@/Components/CMS/Webpage/Iframe/IframeWorkshop.vue'
import BentoGrid from '@/Components/CMS/Webpage/BentoGrid/BentoGridWorksop.vue'
import Department from '@/Components/CMS/Webpage/Department/DepartmentWorkshop.vue'
import Overview from '@/Components/CMS/Webpage/Overview/OverviewWorkshop.vue'
import Script from '@/Components/CMS/Webpage/Script/ScriptWorkShop.vue'
import  CtaAurora1 from "@/Components/CMS/Webpage/CTAAurora1/CtaAurora1Workshop.vue"
import Overview2 from "@/Components/CMS/Webpage/Overview2/Overview2Workshop.vue"
import Footer1 from '@/Components/CMS/Website/Footers/footerTheme1/Footer1Workshop.vue'
import Topbar1 from '@/Components/CMS/Website/TopBars/Template/Topbar1/Topbar1Workshop.vue'
import Topbar2 from '@/Components/CMS/Website/TopBars/Template/Topbar2/Topbar2Workshop.vue'
import Topbar3 from '@/Components/CMS/Website/TopBars/Template/Topbar3/Topbar3Workshop.vue'
import Header1 from '@/Components/CMS/Website/Headers/Header1/Header1Workshop.vue'
import Header2 from '@/Components/CMS/Website/Headers/Header2/Header2Workshop.vue'
import Menu1 from '@/Components/CMS/Website/Menus/Menu1Workshop.vue'
import TextColumn from '@/Components/CMS/Webpage/TextColumn/TextColumnWorkshop.vue'
import Topbar1Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar1Fulfilment/Topbar1FulfilmentWorkshop.vue'
import Topbar2Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar2Fulfilment/Topbar2FulfilmentWorkshop.vue' 
import Topbar3Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar3Fulfilment/Topbar3FulfilemntWorkshop.vue'
import AboutUs from '@/Components/CMS/Webpage/AboutUs/AboutUsWorkshop.vue'
import BrandPartners from '@/Components/CMS/Webpage/BrandPartner/BrandPartnersWorkshop.vue'
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
        'images': ImageWorkshop,
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
        'header-2': Header2,
        'menu-1': Menu1,
        'text-column' : TextColumn,
        'top-bar-1-fulfilment': Topbar1Fulfilment,
        'top-bar-2-fulfilment': Topbar2Fulfilment,
        'top-bar-3-fulfilment': Topbar3Fulfilment,
        'about_us': AboutUs,
        'brand_partners': BrandPartners
    }
    return components[componentName] ?? NotFoundComponents
}


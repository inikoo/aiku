import type { Component } from 'vue'

import Footer1Blueprint from '@/Components/CMS/Website/Footers/footerTheme1/bluprint'
import Topbar1Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar1/Blueprint"
import Topbar2Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar2/Blueprint"
import Topbar3Blueprint from "@/Components/CMS/Website/TopBars/Template/Topbar3/Blueprint"
import Header1Blueprint from'@/Components/CMS/Website/Headers/Header1/Blueprint'
import BentoGridBlueprint from '@/Components/CMS/Webpage/BentoGrid/Blueprint'
import CTA2Blueprint from '@/Components/CMS/Webpage/CTA2/Blueprint'
import CategoriesBlueprint from '@/Components/CMS/Webpage/Categories/Blueprint'
import CTA3Blueprint from '@/Components/CMS/Webpage/CTA3/Blueprint'
import CTAAurora1Blueprint from '@/Components/CMS/Webpage/CTAAurora1/Blueprint'
import CTABlueprint from '@/Components/CMS/Webpage/CTA/Blueprint'
import DepartmentBlueprint from '@/Components/CMS/Webpage/Department/Blueprint'
import GalleryBlueprint from '@/Components/CMS/Webpage/Gallery/Blueprint'
import IframeBlueprint from '@/Components/CMS/Webpage/Iframe/Blueprint'
import ImageBlueprint from '@/Components/CMS/Webpage/Image/Blueprint'
import Overview2Blueprint from '@/Components/CMS/Webpage/Overview2/Blueprint'
import OverviewBlueprint from '@/Components/CMS/Webpage/Overview/Blueprint'
import ReviewsBlueprint from '@/Components/CMS/Webpage/Reviews/Blueprint'
import TextBlueprint from '@/Components/CMS/Webpage/Text/Blueprint'
import ScriptBlueprint from '@/Components/CMS/Webpage/Script/Blueprint'
import WowsbarBannerBlueprint from '@/Components/CMS/Webpage/WowsbarBanner/Blueprint'
import SeeAlsoBlueprint from '@/Components/CMS/Webpage/SeeAlso/Blueprint'
import TextColumn from '@/Components/CMS/Webpage/TextColumn/Blueprint'
import Topbar1Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar1Fulfilment/Blueprint'
import Topbar2Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar2Fulfilment/Blueprint'
import Topbar3Fulfilment from '@/Components/CMS/Website/TopBars/Template/Topbar3Fulfilment/Blueprint'
import AboutUS from '@/Components/CMS/Webpage/AboutUs/Blueprint'

export const getBlueprint = (componentName: string) => {
    const components: Component = {
        'footer-1': Footer1Blueprint.blueprint,
        'top-bar-1': Topbar1Blueprint.blueprint,
        'top-bar-2': Topbar2Blueprint.blueprint,
        'top-bar-3': Topbar3Blueprint.blueprint,
        'header-1' : Header1Blueprint.blueprint,
        'banner': WowsbarBannerBlueprint.blueprint,
        "bento-grid-1": BentoGridBlueprint.blueprint,
        "bricks": GalleryBlueprint.blueprint,
        'cta1': CTABlueprint.blueprint,
        'cta2': CTA2Blueprint.blueprint,
        'cta3': CTA3Blueprint.blueprint,
        'text-column' : TextColumn.blueprint,
   /*      "department": .blueprint,
        'family': FamilyPageOffer.blueprint, */
        "iframe": IframeBlueprint.blueprint,
        'images': ImageBlueprint.blueprint,
        "overview_aurora": OverviewBlueprint.blueprint,

/*       'product': ProductPage.blueprint,
        'products': .blueprint, */
        "script": ScriptBlueprint.blueprint,
        'text': TextBlueprint.blueprint,
        'cta_aurora_1' : CTAAurora1Blueprint.blueprint,
        'overview_2' : Overview2Blueprint.blueprint,
        'top-bar-1-fulfilment': Topbar1Fulfilment.blueprint,
        'top-bar-2-fulfilment': Topbar2Fulfilment.blueprint,
        'top-bar-3-fulfilment': Topbar3Fulfilment.blueprint,
        'about_us': AboutUS.blueprint,
    }
    return components[componentName] ?? []
}

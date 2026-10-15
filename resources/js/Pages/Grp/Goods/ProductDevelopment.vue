<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { capitalize } from "@/Composables/capitalize"
import { useTabChange } from "@/Composables/tab-change"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import type { Navigation } from "@/types/Tabs"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import ProductDevelopmentStageBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentStageBar.vue"
import ProductDevelopmentBrief from "@/Components/Goods/ProductDevelopment/ProductDevelopmentBrief.vue"
import ProductDevelopmentProducts from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProducts.vue"
import ProductDevelopmentCompliance from "@/Components/Goods/ProductDevelopment/ProductDevelopmentCompliance.vue"
import ProductDevelopmentComingSoon from "@/Components/Goods/ProductDevelopment/ProductDevelopmentComingSoon.vue"
import ProductDevelopmentMissingBadge from "@/Components/Goods/ProductDevelopment/ProductDevelopmentMissingBadge.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faClipboard, faAtom, faShieldCheck, faPencilRuler, faPaperclip, faTasks, faClock } from "@fal"
library.add(faLightbulbOn, faClipboard, faAtom, faShieldCheck, faPencilRuler, faPaperclip, faTasks, faClock)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    header: {
        type: string
        status: string
        status_icon: { icon: string, class: string, tooltip: string }
        brand: string | null
        family: { code: string, name: string, route: routeType } | null
        supplier: string | null
        agent: string | null
        owner: string | null
        target_online_date: string | null
        number_items: number
        number_missing: number
    }
    stages: any[]
    stageMove: any
    tabs: {
        current: string
        navigation: Navigation
    }
    brief?: object
    products?: object
    compliance?: object
    activity?: object
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Record<string, any> = {
        brief: ProductDevelopmentBrief,
        products: ProductDevelopmentProducts,
        compliance: ProductDevelopmentCompliance,
        design: ProductDevelopmentComingSoon,
        files: ProductDevelopmentComingSoon,
        tasks: ProductDevelopmentComingSoon,
        activity: TableHistories,
    }

    return components[currentTab.value]
})

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : "—"
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 border-b border-gray-200 px-4 py-3 text-sm sm:grid-cols-3 lg:grid-cols-6">
        <div>
            <dt class="text-gray-500">{{ ctrans("Type") }}</dt>
            <dd class="flex items-center gap-1.5 text-gray-800">
                <FontAwesomeIcon :icon="header.status_icon.icon" :class="header.status_icon.class" v-tooltip="header.status_icon.tooltip" fixed-width aria-hidden="true" />
                {{ header.type }}
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ ctrans("Brand / family") }}</dt>
            <dd class="text-gray-800">
                {{ header.brand ?? "—" }}
                <template v-if="header.family">
                    · <Link :href="route(header.family.route.name, header.family.route.parameters)" class="primaryLink">{{ header.family.code }}</Link>
                </template>
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ ctrans("Supplier / agent") }}</dt>
            <dd class="text-gray-800">{{ [header.supplier, header.agent].filter(Boolean).join(" · ") || "—" }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ ctrans("Owner") }}</dt>
            <dd class="text-gray-800">{{ header.owner ?? "—" }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ ctrans("Target online") }}</dt>
            <dd class="tabular-nums text-gray-800">{{ formatDate(header.target_online_date) }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ ctrans(":count SKUs", { count: String(header.number_items) }) }}</dt>
            <dd><ProductDevelopmentMissingBadge :count="header.number_missing" /></dd>
        </div>
    </dl>

    <ProductDevelopmentStageBar :stages="stages" :stageMove="stageMove" />

    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>

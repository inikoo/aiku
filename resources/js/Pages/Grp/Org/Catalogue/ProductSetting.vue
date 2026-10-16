<script setup lang="ts">
import { computed, nextTick, ref, watch } from "vue"
import { Head, router } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExternalLink, faSlidersH } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import WorkflowSteps from "@/Components/DataDisplay/WorkflowSteps.vue"
import ProductChecklistCard from "@/Components/Catalogue/ProductSetting/ProductChecklistCard.vue"
import ProductSettingForm from "@/Components/Catalogue/ProductSetting/ProductSettingForm.vue"
import SeoReviewSuggestions from "@/Components/Catalogue/ProductSetting/SeoReviewSuggestions.vue"
import SeoWebmasterValidation from "@/Components/Catalogue/ProductSetting/SeoWebmasterValidation.vue"
import WebpageIssues from "@/Components/Catalogue/ProductSetting/WebpageIssues.vue"
import { productSettingActionsId, type MasterProductData, type ProductSettingTab, type ShopProductSetting } from "@/Components/Catalogue/ProductSetting/types"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faSlidersH)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    tab: ProductSettingTab
    canEdit: boolean
    overview_route: routeType
    master: MasterProductData
    shop: ShopProductSetting
}>()

const currentTab = ref<ProductSettingTab>(props.tab)
watch(() => props.tab, (tab) => (currentTab.value = tab))

const settingUrl = (tab: ProductSettingTab) => {
    const url = new URL(window.location.href)
    url.searchParams.set("tab", tab)

    return `${url.pathname}?${url.searchParams.toString()}`
}

const selectTab = (tab: ProductSettingTab) => {
    currentTab.value = tab
    router.get(settingUrl(tab), {}, { preserveState: true, preserveScroll: true, replace: true, only: ["tab"] })
}

const subMenu = computed(() => [
    { key: "setting" as ProductSettingTab, label: ctrans("Setting") },
    { key: "seo_review" as ProductSettingTab, label: ctrans("SEO review & suggestions") },
    { key: "validation" as ProductSettingTab, label: ctrans("Webmaster validation") },
    { key: "issues" as ProductSettingTab, label: ctrans("Issues"), count: props.shop?.audit?.issue_count },
])

const openWorkshop = () => {
    const workshopRoute = props.shop?.webpage?.workshop_route
    if (workshopRoute) {
        window.open(route(workshopRoute.name, workshopRoute.parameters), "_blank")
    }
}

const settingForm = ref<InstanceType<typeof ProductSettingForm> | null>(null)

const openSetting = async (field: string) => {
    selectTab("setting")
    await nextTick()
    settingForm.value?.focusField(field)
}

const selectWorkflowStep = (stepKey: string) => selectTab(stepKey === "seo_review" ? "seo_review" : "validation")

const goToOverview = () => router.visit(route(props.overview_route.name, props.overview_route.parameters))

const reloadShop = () => router.reload({ only: ["shop"] })
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="space-y-4 p-4">
        <nav class="flex flex-wrap gap-1 border-b border-gray-200 pb-2" :aria-label="ctrans('Product setting')">
            <template v-for="item in subMenu" :key="item.key">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="currentTab === item.key ? 'bg-[--app-accent-soft] text-[--app-accent-strong]' : 'text-gray-700 hover:bg-gray-100'"
                    :aria-current="currentTab === item.key ? 'page' : undefined"
                    @click="selectTab(item.key)"
                >
                    {{ item.label }}
                    <span v-if="item.count" class="rounded-full bg-red-700 px-1.5 text-xs font-semibold text-white">{{ item.count }}</span>
                </button>
                <button
                    v-if="item.key === 'setting' && shop.webpage?.workshop_route"
                    type="button"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100"
                    @click="openWorkshop"
                >
                    {{ ctrans("Workshop") }}
                    <FontAwesomeIcon :icon="faExternalLink" class="text-xs text-gray-500" fixed-width aria-hidden="true" />
                </button>
            </template>
        </nav>

        <WorkflowSteps
            v-if="currentTab === 'seo_review' || currentTab === 'validation'"
            :steps="shop.workflow.steps"
            :current="shop.workflow.current"
            :selectableKeys="['seo_review', 'suggestions_implemented']"
            @select="selectWorkflowStep"
        />

        <WebpageIssues
            v-if="currentTab === 'issues'"
            :audit="shop.audit ?? { last_crawl_at: null, issue_count: 0, issue_groups: [] }"
            :crawlRoute="shop.webpage?.crawl_route ?? null"
        />

        <div v-if="currentTab !== 'issues'" :id="productSettingActionsId" class="empty:hidden" />

        <div v-if="currentTab !== 'issues'" class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div>
                <ProductSettingForm
                    v-if="currentTab === 'setting'"
                    ref="settingForm"
                    :master="master"
                    :shop="shop"
                    :canEdit="canEdit"
                    @saved="reloadShop"
                />

                <template v-else-if="!shop.review">
                    <div class="rounded-lg border border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-500">
                        {{ ctrans("This product has no webpage in the shop") }}
                    </div>
                </template>

                <SeoReviewSuggestions
                    v-else-if="currentTab === 'seo_review'"
                    :review="shop.review"
                    :canEdit="shop.can_edit_seo"
                    :shopCode="shop.shop.code"
                    @sent="goToOverview"
                />

                <SeoWebmasterValidation
                    v-else
                    :review="shop.review"
                    @openSetting="openSetting"
                    @validated="goToOverview"
                />
            </div>

            <aside class="h-fit lg:sticky lg:top-4">
                <ProductChecklistCard
                    :checklist="shop.checklist"
                    :note="currentTab === 'setting' ? undefined : ctrans('Current values on the live page. Suggestions change these once the webmaster implements them.')"
                />
            </aside>
        </div>
    </div>
</template>

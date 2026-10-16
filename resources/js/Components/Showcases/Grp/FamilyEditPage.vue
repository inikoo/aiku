<script setup lang="ts">
import { nextTick, ref } from "vue"
import { Message } from "primevue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExternalLink } from "@fal"
import FamilyEditCard, { type FamilyEditCardData } from "@/Components/Showcases/Grp/FamilyEditCard.vue"
import FamilySeoChecklist from "@/Components/Showcases/Grp/FamilySeoChecklist.vue"
import FamilySeoReview from "@/Components/Showcases/Grp/FamilySeoReview.vue"
import FamilyWebmasterValidation from "@/Components/Showcases/Grp/FamilyWebmasterValidation.vue"
import FamilySeoIssues from "@/Components/Showcases/Grp/FamilySeoIssues.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

defineProps<{
    data: {
        notice: string | null
        workshop_route: routeType | null
        sections: { title: string, subtitle: string, cards: FamilyEditCardData[] }[]
        page_score: any | null
        seo_review: any | null
        webmaster_validation: any | null
        seo_issues: any | null
    }
}>()

const currentSection = ref<"page_setting" | "seo_review" | "webmaster_validation" | "issues">("page_setting")

const navClass = (section: string) => currentSection.value === section
    ? "font-bold bg-[--app-accent-soft] text-[--app-accent]"
    : "font-semibold text-gray-700 hover:bg-gray-100"

const openSetting = async (cardKey: string) => {
    currentSection.value = "page_setting"
    await nextTick()
    document.getElementById(`edit-page-card-${cardKey}`)?.scrollIntoView({ behavior: "smooth", block: "start" })
}
</script>

<template>
    <div class="p-4 md:p-6 flex flex-col gap-5">
        <nav :aria-label="ctrans('Edit page sections')" class="flex flex-wrap gap-1.5">
            <button type="button" :aria-current="currentSection === 'page_setting' ? 'page' : undefined" @click="currentSection = 'page_setting'"
                class="inline-flex items-center min-h-10 px-3.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="navClass('page_setting')">
                {{ ctrans("Page setting") }}
            </button>
            <a v-if="data.workshop_route" :href="route(data.workshop_route.name, data.workshop_route.parameters)" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 min-h-10 px-3.5 rounded-lg font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[--app-accent]">
                {{ ctrans("Workshop") }}
                <FontAwesomeIcon :icon="faExternalLink" class="text-xs" fixed-width aria-hidden="true" />
                <span class="sr-only">({{ ctrans("opens in a new window") }})</span>
            </a>
            <button v-if="data.seo_review" type="button" :aria-current="currentSection === 'seo_review' ? 'page' : undefined" @click="currentSection = 'seo_review'"
                class="inline-flex items-center min-h-10 px-3.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="navClass('seo_review')">
                {{ ctrans("SEO reviews and suggestions") }}
            </button>
            <button v-if="data.webmaster_validation" type="button" :aria-current="currentSection === 'webmaster_validation' ? 'page' : undefined" @click="currentSection = 'webmaster_validation'"
                class="inline-flex items-center min-h-10 px-3.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="navClass('webmaster_validation')">
                {{ ctrans("Webmaster validation") }}
            </button>
            <button v-if="data.seo_issues" type="button" :aria-current="currentSection === 'issues' ? 'page' : undefined" @click="currentSection = 'issues'"
                class="inline-flex items-center gap-1.5 min-h-10 px-3.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="navClass('issues')">
                {{ ctrans("Issues") }}
                <span v-if="data.seo_issues.total" class="min-w-[18px] px-1.5 rounded-full bg-red-700 text-white text-[11px] font-bold text-center">{{ data.seo_issues.total }}</span>
            </button>
        </nav>

        <Message v-if="data.notice" severity="warn">{{ data.notice }}</Message>

        <FamilySeoReview v-if="currentSection === 'seo_review' && data.seo_review" :data="data.seo_review" :pageScore="data.page_score" />

        <FamilyWebmasterValidation v-else-if="currentSection === 'webmaster_validation' && data.webmaster_validation" :data="data.webmaster_validation"
            :sections="data.sections" :workshopRoute="data.workshop_route" :pageScore="data.page_score" @openSetting="openSetting" />

        <FamilySeoIssues v-else-if="currentSection === 'issues' && data.seo_issues" :data="data.seo_issues" />

        <div v-else class="flex flex-wrap gap-5 items-start">
            <div class="flex-[999_1_680px] min-w-0 flex flex-col gap-5">
                <section v-for="(section, sectionIndex) in data.sections" :key="sectionIndex"
                    class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ section.title }}</h2>
                        <span class="text-xs text-gray-500">{{ section.subtitle }}</span>
                    </div>

                    <FamilyEditCard v-for="(card, cardIndex) in section.cards" :key="cardIndex" :id="card.key ? `edit-page-card-${card.key}` : undefined"
                        :card="card" :fieldKey="`${sectionIndex}-${cardIndex}`" />
                </section>
            </div>

            <aside class="flex-[1_1_300px] flex flex-col gap-5">
                <FamilySeoChecklist v-if="data.page_score" :pageScore="data.page_score" />
            </aside>
        </div>
    </div>
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import DashboardSettings from '@/Components/DataDisplay/Dashboard/DashboardSettings.vue'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import SegmentedToggle from '@/Components/Utils/SegmentedToggle.vue'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import UploadReports from '@/Components/Upload/UploadReports.vue'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import { capitalize } from '@/Composables/capitalize'
import { ctrans } from '@/Composables/useTrans'
import { useLocaleStore } from '@/Stores/locale'
import { useFormatTime } from '@/Composables/useFormatTime'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
    faAd, faArrowDown, faArrowUp, faBookmark, faChartLine, faClone, faExternalLink, faFileExport, faFilm,
    faHashtag, faHeart, faHistory, faImage, faInfoCircle, faSpinnerThird, faTimes, faUpload, faVideo,
} from '@fal'
import { PageHeadingTypes } from '@/types/PageHeading'
import { routeType } from '@/types/route'
import { Intervals, Settings } from '@/types/Components/Dashboard'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend)
library.add(faAd, faArrowDown, faArrowUp, faBookmark, faChartLine, faClone, faExternalLink, faFileExport, faFilm,
    faHashtag, faHeart, faHistory, faImage, faInfoCircle, faSpinnerThird, faTimes, faUpload, faVideo)

interface Kpis {
    posts: number
    reach: number
    impressions: number
    engagements: number
    engagement_rate: number | null
    link_clicks: number
    followers: number | null
    follower_growth: number | null
    spend: number
    revenue: number
    orders: number
    roas: number | null
    cpa: number | null
    ad_impressions: number
    ad_clicks: number
    ctr: number | null
}

interface Post {
    id: number
    description: string | null
    permalink: string | null
    published_at: string
    type: string
    type_label: string
    platform: string
    platform_label: string
    account_name: string
    shop_name: string
    reach: number
    impressions: number
    reactions: number
    comments: number
    shares: number
    saves: number
    link_clicks: number
    engagements: number
    engagement_rate: number
}

interface Performance {
    from: string | null
    to: string | null
    previous_from: string | null
    previous_to: string | null
    period: string
    period_label: string
    currency_code: string
    filters: { shop: string | null, country: string | null, platform: string | null }
    options: {
        shops: { id: number, slug: string, name: string, country_code: string, has_accounts: boolean }[]
        countries: { code: string, name: string }[]
        platforms: { value: string, label: string }[]
    }
    data_until: string | null
    kpis: { current: Kpis, previous: Kpis | null }
    trend: { bucket: 'day' | 'week' | 'month', points: { date: string, posts: number, reach: number, engagements: number, followers: number | null }[] }
    platforms: { platform: string, label: string, posts: number, reach: number, share: number | null, engagements: number, engagement_rate: number | null, followers: number | null }[]
    formats: { type: string, label: string, posts: number, reach: number, average_reach: number, engagements: number, saves: number, shares: number, engagement_rate: number | null }[]
    top_posts: Post[]
    accounts: { id: number, name: string, username: string | null, platform: string, platform_label: string, shop_name: string, shop_slug: string, country_code: string, followers: number | null, followers_at: string | null, posts: number, reach: number, engagements: number, link_clicks: number, engagement_rate: number | null }[]
    paid: {
        channels: { type: string, label: string, spend: number, revenue: number, orders: number, roas: number | null, impressions: number, clicks: number, ctr: number | null }[]
        campaigns: { name: string, channel: string, owner: string | null, spend: number, revenue: number, orders: number, impressions: number, clicks: number, ctr: number | null, cpa: number | null, roas: number | null }[]
    }
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    intervals: Intervals
    settings: Settings
    upload_shops: { id: number, name: string, route: routeType }[]
    export_route: routeType
    performance?: Performance
    uploads?: { data: any[], meta?: any }
}>()

const locale = useLocaleStore()
const layout = inject('layout', layoutStructure)

const currency = computed(() => props.performance?.currency_code ?? layout.group?.currency?.code ?? 'GBP')
const money = (value: number | null | undefined) => value === null || value === undefined ? '–' : locale.currencyFormat(currency.value, value)
const count = (value: number | null | undefined) => value === null || value === undefined ? '–' : locale.number(value)
const short = (value: number | null | undefined) => value === null || value === undefined ? '–' : locale.numberShort(value)
const pct = (value: number | null | undefined, decimals = 1) => value === null || value === undefined ? '–' : value.toFixed(decimals) + '%'
const ratio = (value: number | null | undefined) => value === null || value === undefined ? '–' : value.toFixed(2) + '×'
const day = (value: string | null | undefined) => value ? useFormatTime(value, { formatTime: 'aiku' }) : ''

/* Tabs are client-side: every tab reads the same deferred prop, so switching costs no request. */
const currentTab = ref('overview')
const tabs = {
    overview: { title: ctrans('Overview'), icon: 'fal fa-chart-line' },
    organic: { title: ctrans('Organic'), icon: 'fal fa-heart' },
    paid: { title: ctrans('Paid'), icon: 'fal fa-ad' },
    uploads: { title: ctrans('Upload history'), icon: 'fal fa-upload', icon_badge: 'fal fa-history', type: 'icon', align: 'right' },
}

/* Filters live in the URL so the export link, the period change and a shared link all agree on
   what is on screen. An empty string, not a dropped key, clears a filter: Inertia merges data into
   the current query string, so a dropped key would keep the old value. */
const shopFilter = ref<string | null>(null)
const countryFilter = ref<string | null>(null)
const platformFilter = ref<string | null>(null)
const isReloading = ref(false)

watch(() => props.performance?.filters, (filters) => {
    shopFilter.value = filters?.shop ?? null
    countryFilter.value = filters?.country ?? null
    platformFilter.value = filters?.platform ?? null
}, { immediate: true })

const filterQuery = computed(() => ({
    shop: shopFilter.value ?? '',
    country: countryFilter.value ?? '',
    platform: platformFilter.value ?? '',
}))

const applyFilters = () => {
    router.reload({
        data: filterQuery.value,
        only: ['performance'],
        onStart: () => { isReloading.value = true },
        onFinish: () => { isReloading.value = false },
    })
}

const exportHref = computed(() => route(props.export_route.name, {
    ...(props.export_route.parameters as object),
    ...Object.fromEntries(Object.entries(filterQuery.value).filter(([, value]) => value !== '')),
}))

const selectPt = { label: { class: '!py-1 !text-xs' }, root: { class: '!border-gray-300 !shadow-none' } }

/* Deltas against the previous period. Rates compare in percentage points, everything else as a
   percentage change; a missing or zero previous figure hides the delta rather than showing ∞. */
interface Delta { text: string, direction: 'up' | 'down' | 'flat', good: boolean }

const delta = (current: number | null | undefined, previous: number | null | undefined, options: { points?: boolean, lowerIsBetter?: boolean } = {}): Delta | null => {
    if (current === null || current === undefined || previous === null || previous === undefined) return null
    let change: number
    let text: string
    if (options.points) {
        change = current - previous
        text = Math.abs(change).toFixed(1) + ' pp'
    } else {
        if (previous === 0) return null
        change = (current - previous) / Math.abs(previous) * 100
        text = Math.abs(change).toFixed(1) + '%'
    }
    const direction = Math.abs(change) < 0.05 ? 'flat' : change > 0 ? 'up' : 'down'
    const good = direction === 'flat' || (options.lowerIsBetter ? direction === 'down' : direction === 'up')

    return { text, direction, good }
}

interface Kpi { key: string, label: string, value: string, delta: Delta | null, sub?: string, help?: string }

const kpi = (key: string, label: string, value: string, change: Delta | null, sub?: string, help?: string): Kpi => ({ key, label, value, delta: change, sub, help })

const current = computed(() => props.performance?.kpis.current)
const previous = computed(() => props.performance?.kpis.previous ?? null)

const followersSub = computed(() => {
    const growth = current.value?.follower_growth
    if (growth === null || growth === undefined || growth === 0) return undefined
    return (growth > 0 ? '+' : '−') + count(Math.abs(growth)) + ' ' + ctrans('this period')
})

const overviewKpis = computed<Kpi[]>(() => {
    const c = current.value
    const p = previous.value
    if (!c) return []
    return [
        kpi('reach', ctrans('Total reach'), short(c.reach), delta(c.reach, p?.reach), count(c.posts) + ' ' + ctrans('posts')),
        kpi('engagement_rate', ctrans('Engagement rate'), pct(c.engagement_rate), delta(c.engagement_rate, p?.engagement_rate, { points: true }), short(c.engagements) + ' ' + ctrans('engagements'), ctrans('Reactions, comments, shares and saves divided by reach.')),
        kpi('followers', ctrans('Followers'), c.followers === null ? '–' : short(c.followers), null, followersSub.value, ctrans('Followers across every account at the latest imported day. Growth is the change over the period.')),
        kpi('link_clicks', ctrans('Link clicks'), short(c.link_clicks), delta(c.link_clicks, p?.link_clicks)),
        kpi('spend', ctrans('Paid spend'), money(c.spend), delta(c.spend, p?.spend, { lowerIsBetter: true }), c.orders ? count(c.orders) + ' ' + ctrans('orders') : undefined),
        kpi('roas', ctrans('ROAS'), ratio(c.roas), delta(c.roas, p?.roas), c.revenue ? money(c.revenue) + ' ' + ctrans('attributed') : undefined, ctrans('Attributed revenue divided by spend.')),
    ]
})

const organicKpis = computed<Kpi[]>(() => {
    const c = current.value
    const p = previous.value
    if (!c) return []
    return [
        kpi('posts', ctrans('Posts'), count(c.posts), delta(c.posts, p?.posts)),
        kpi('reach', ctrans('Reach'), short(c.reach), delta(c.reach, p?.reach)),
        kpi('impressions', ctrans('Impressions / views'), short(c.impressions), delta(c.impressions, p?.impressions)),
        kpi('engagements', ctrans('Engagements'), short(c.engagements), delta(c.engagements, p?.engagements)),
        kpi('engagement_rate', ctrans('Engagement rate'), pct(c.engagement_rate), delta(c.engagement_rate, p?.engagement_rate, { points: true })),
        kpi('followers', ctrans('Followers'), c.followers === null ? '–' : short(c.followers), null, followersSub.value),
    ]
})

const paidKpis = computed<Kpi[]>(() => {
    const c = current.value
    const p = previous.value
    if (!c) return []
    return [
        kpi('spend', ctrans('Spend'), money(c.spend), delta(c.spend, p?.spend, { lowerIsBetter: true })),
        kpi('revenue', ctrans('Revenue'), money(c.revenue), delta(c.revenue, p?.revenue), c.orders ? count(c.orders) + ' ' + ctrans('orders') : undefined),
        kpi('roas', ctrans('ROAS'), ratio(c.roas), delta(c.roas, p?.roas)),
        kpi('cpa', ctrans('CPA'), money(c.cpa), delta(c.cpa, p?.cpa, { lowerIsBetter: true }), undefined, ctrans('Spend divided by attributed orders.')),
        kpi('ctr', ctrans('CTR'), pct(c.ctr, 2), delta(c.ctr, p?.ctr, { points: true }), short(c.ad_impressions) + ' ' + ctrans('impressions')),
        kpi('clicks', ctrans('Clicks'), short(c.ad_clicks), delta(c.ad_clicks, p?.ad_clicks)),
    ]
})

/* Chart colours. Reach is the dark green of the brief's mock-up, engagements amber, followers teal;
   validated together with the dataviz palette checker. Platforms and formats are identity colours
   that always sit beside their label, never colour alone. */
const SERIES = { reach: '#3b7a2a', engagements: '#d97706', followers: '#0d9488' }
const PLATFORM_COLORS: Record<string, string> = { facebook: '#475569', instagram: '#e11d48', tiktok: '#0d9488' }
const platformColor = (platform: string) => PLATFORM_COLORS[platform] ?? '#9ca3af'

const FORMAT_STYLE: Record<string, { icon: string, color: string }> = {
    reel: { icon: 'fal fa-film', color: '#e11d48' },
    carousel: { icon: 'fal fa-clone', color: '#d97706' },
    image: { icon: 'fal fa-image', color: '#3b7a2a' },
    video: { icon: 'fal fa-video', color: '#0d9488' },
    story: { icon: 'fal fa-history', color: '#475569' },
}
const formatStyle = (type: string) => FORMAT_STYLE[type] ?? FORMAT_STYLE[Object.keys(FORMAT_STYLE).find(key => type?.toLowerCase().includes(key)) ?? ''] ?? { icon: 'fal fa-hashtag', color: '#9ca3af' }

const bucketLabel = (date: string) => {
    const parsed = new Date(date)
    if (props.performance?.trend.bucket === 'month') return parsed.toLocaleDateString(undefined, { month: 'short', year: '2-digit' })
    return parsed.toLocaleDateString(undefined, { day: 'numeric', month: 'short' })
}

const trendLabels = computed(() => (props.performance?.trend.points ?? []).map(point => bucketLabel(point.date)))
const hasTrend = computed(() => (props.performance?.trend.points ?? []).some(point => point.reach > 0 || point.engagements > 0))
const hasFollowersTrend = computed(() => (props.performance?.trend.points ?? []).some(point => point.followers !== null))

const lineDataset = (label: string, data: number[], color: string, fill: boolean) => ({
    label,
    data,
    borderColor: color,
    backgroundColor: fill ? color + '22' : 'transparent',
    fill,
    tension: 0.3,
    pointRadius: 0,
    pointHoverRadius: 4,
    pointHoverBackgroundColor: color,
    borderWidth: 2,
})

/* Reach and engagements live an order of magnitude apart, so they share the x axis as two stacked
   panes rather than fighting over one y axis or inventing a second one. */
const reachChart = computed(() => ({
    labels: trendLabels.value,
    datasets: [lineDataset(ctrans('Reach'), (props.performance?.trend.points ?? []).map(point => point.reach), SERIES.reach, true)],
}))
const engagementsChart = computed(() => ({
    labels: trendLabels.value,
    datasets: [lineDataset(ctrans('Engagements'), (props.performance?.trend.points ?? []).map(point => point.engagements), SERIES.engagements, false)],
}))
const followersChart = computed(() => ({
    labels: trendLabels.value,
    datasets: [lineDataset(ctrans('Followers'), (props.performance?.trend.points ?? []).map(point => point.followers ?? NaN), SERIES.followers, false)],
}))

const chartOptions = (showX: boolean, beginAtZero = true) => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#111827',
            titleFont: { size: 11 },
            bodyFont: { size: 11 },
            padding: 8,
            displayColors: false,
            callbacks: {
                label: (context: any) => context.dataset.label + ': ' + (Number.isNaN(context.parsed.y) ? '–' : locale.number(context.parsed.y)),
            },
        },
    },
    scales: {
        x: { display: showX, grid: { display: false }, ticks: { maxTicksLimit: 10, maxRotation: 0, font: { size: 10 }, color: '#6b7280' } },
        y: {
            beginAtZero,
            grid: { color: 'rgba(0,0,0,0.05)' },
            border: { display: false },
            ticks: { maxTicksLimit: 4, font: { size: 10 }, color: '#6b7280', callback: (value: any) => locale.numberShort(Number(value)) },
        },
    },
})

const maxOf = (values: number[]) => Math.max(0, ...values)
const barWidth = (value: number, max: number) => max > 0 ? Math.max(1, value / max * 100) + '%' : '0%'

const platformRows = computed(() => (props.performance?.platforms ?? []).filter(row => row.posts > 0 || row.reach > 0))
const formatRows = computed(() => [...(props.performance?.formats ?? [])].filter(row => row.posts > 0).sort((a, b) => b.average_reach - a.average_reach))
const maxFormatReach = computed(() => maxOf(formatRows.value.map(row => row.average_reach)))

/* Top content: sorted on the client so the ranking question (by rate, by reach, by volume) is one
   click, and the long tail opens on request rather than filling the screen. */
type PostSort = 'engagement_rate' | 'reach' | 'engagements'
const postSort = ref<PostSort>('engagement_rate')
const postSortOptions = [
    { label: ctrans('Engagement rate'), value: 'engagement_rate' as PostSort },
    { label: ctrans('Reach'), value: 'reach' as PostSort },
    { label: ctrans('Engagements'), value: 'engagements' as PostSort },
]
const showAllPosts = ref(false)
const sortedPosts = computed(() => [...(props.performance?.top_posts ?? [])].sort((a, b) => (b[postSort.value] ?? 0) - (a[postSort.value] ?? 0)))
const visiblePosts = computed(() => sortedPosts.value.slice(0, showAllPosts.value ? 50 : 10))
const overviewPosts = computed(() => [...(props.performance?.top_posts ?? [])].sort((a, b) => b.engagement_rate - a.engagement_rate).slice(0, 5))
const postTitle = (post: Post) => post.description?.trim() || ctrans('Untitled :type', { type: post.type_label })

const accounts = computed(() => [...(props.performance?.accounts ?? [])].sort((a, b) => b.reach - a.reach))

const paidChannels = computed(() => (props.performance?.paid.channels ?? []).filter(channel => channel.spend > 0 || channel.impressions > 0))
const maxChannelSpend = computed(() => maxOf(paidChannels.value.map(channel => channel.spend)))
const campaigns = computed(() => [...(props.performance?.paid.campaigns ?? [])].sort((a, b) => b.spend - a.spend))
const hasPaid = computed(() => paidChannels.value.length > 0 || campaigns.value.length > 0)
const hasOrganic = computed(() => props.performance?.data_until !== null && props.performance?.data_until !== undefined)

/* Every ROAS pill says the same thing: paid back or not. Colour is information here. */
const roasClass = (roas: number | null) => roas === null ? 'bg-gray-100 text-gray-400' : roas >= 1 ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'

/* Upload: the import lands on one shop, so the shop is chosen first and the modal opens after. */
const isUploadOpen = ref(false)
const isShopPickerOpen = ref(false)
const uploadShop = ref<{ id: number, name: string, route: routeType } | null>(null)
const uploadShopOptions = computed(() => props.upload_shops ?? [])

const startUpload = () => {
    if (uploadShopOptions.value.length === 1) {
        uploadShop.value = uploadShopOptions.value[0]
        isUploadOpen.value = true
        return
    }
    isShopPickerOpen.value = true
}

const continueUpload = () => {
    if (!uploadShop.value) return
    isShopPickerOpen.value = false
    isUploadOpen.value = true
}

const uploadSpreadsheet = computed(() => uploadShop.value ? {
    event: 'action-progress',
    channel: 'grp.personal.' + layout.group?.id,
    required_fields: [],
    template: { label: '' },
    route: { upload: uploadShop.value.route },
} as any : undefined)

const uploadInformation = ctrans('Upload the CSV or Excel exports from Meta Business Suite (Insights → Export data → Content, or Results/Audience per day) and TikTok Studio. Content exports add or update posts; daily exports add followers, reach and visits per day. Re-uploading a period updates its figures, it never duplicates them.')
</script>

<template>
  <div>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <a :href="exportHref" download>
                <Button icon="fal fa-file-export" :label="ctrans('Export posts')" type="tertiary" />
            </a>
            <Button v-if="uploadShopOptions.length" icon="fal fa-upload" :label="ctrans('Import export')" type="tertiary" @click="startUpload" />
        </template>
    </PageHeading>

    <div class="pt-3">
        <DashboardSettings :intervals="intervals" :settings="settings" currentTab="social_media" :reloadOnly="['performance', 'intervals']" />
    </div>

    <div class="px-4 py-4 space-y-4 max-w-[1600px]">
        <Deferred data="performance">
            <template #fallback>
                <div class="space-y-4">
                    <div class="h-8 w-full max-w-2xl animate-pulse rounded-md bg-gray-100" />
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                        <div v-for="card in 6" :key="card" class="h-24 animate-pulse rounded-xl bg-gray-100 ring-1 ring-gray-200" />
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 h-72 animate-pulse rounded-xl bg-gray-100 ring-1 ring-gray-200" />
                        <div class="h-72 animate-pulse rounded-xl bg-gray-100 ring-1 ring-gray-200" />
                    </div>
                    <div class="h-56 animate-pulse rounded-xl bg-gray-100 ring-1 ring-gray-200" />
                </div>
            </template>

            <div v-if="performance" class="space-y-4">
                <!-- Scope: the sentence the whole screen is about, and the filters that change it -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 mr-1">{{ ctrans('Organic and paid social in one view') }} ·</span>
                    <span v-if="performance.data_until" class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700 ring-1 ring-green-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500" />
                        {{ ctrans('Data up to') }} {{ day(performance.data_until) }}
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700 ring-1 ring-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                        {{ ctrans('No data imported yet') }}
                    </span>
                    <FontAwesomeIcon v-if="isReloading" icon="fal fa-spinner-third" spin class="text-gray-400 text-xs" fixed-width />

                    <div class="ml-auto flex flex-wrap items-center gap-2">
                        <Select v-model="shopFilter" :options="performance.options.shops" optionLabel="name" optionValue="slug" showClear
                                :placeholder="ctrans('All brands')" class="w-44" :pt="selectPt" @change="applyFilters" />
                        <Select v-model="countryFilter" :options="performance.options.countries" optionLabel="name" optionValue="code" showClear
                                :placeholder="ctrans('All markets')" class="w-40" :pt="selectPt" @change="applyFilters" />
                        <Select v-model="platformFilter" :options="performance.options.platforms" optionLabel="label" optionValue="value" showClear
                                :placeholder="ctrans('All platforms')" class="w-40" :pt="selectPt" @change="applyFilters" />
                    </div>
                </div>

                <Tabs :current="currentTab" :navigation="tabs" @update:tab="(tab: string) => currentTab = tab" />

                <div class="space-y-4 transition-opacity" :class="isReloading ? 'opacity-50 pointer-events-none' : ''">
                    <!-- Empty state: nothing imported yet, say how to start -->
                    <div v-if="!hasOrganic && currentTab !== 'paid' && currentTab !== 'uploads'" class="rounded-xl ring-1 ring-gray-200 bg-white p-6 flex flex-col md:flex-row gap-6 items-start">
                        <div class="h-12 w-12 shrink-0 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                            <FontAwesomeIcon icon="fal fa-upload" class="text-lg" fixed-width />
                        </div>
                        <div class="space-y-2 text-sm text-gray-600 max-w-2xl">
                            <div class="text-base font-medium text-gray-800">{{ ctrans('No social media data for this period yet') }}</div>
                            <p>{{ ctrans('This dashboard is fed by the exports the platforms already produce. Nothing is typed in by hand.') }}</p>
                            <ol class="list-decimal pl-5 space-y-1">
                                <li>{{ ctrans('Meta Business Suite: Insights → Export data → Content, for the period you want; Results or Audience per day for followers and reach.') }}</li>
                                <li>{{ ctrans('TikTok Studio: Analytics → Download data, as CSV.') }}</li>
                                <li>{{ ctrans('Upload the file here with Import export, choosing the brand it belongs to. Re-uploading a period updates it, it never duplicates it.') }}</li>
                            </ol>
                            <Button v-if="uploadShopOptions.length" icon="fal fa-upload" :label="ctrans('Import export')" type="tertiary" class="mt-2" @click="startUpload" />
                        </div>
                    </div>

                    <!-- Overview -->
                    <template v-if="currentTab === 'overview'">
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            <div v-for="card in overviewKpis" :key="card.key" class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                                <div class="text-xs text-gray-400 flex items-center gap-1">
                                    {{ card.label }}
                                    <FontAwesomeIcon v-if="card.help" v-tooltip="card.help" icon="fal fa-info-circle" class="text-gray-300 cursor-help" fixed-width />
                                </div>
                                <div class="mt-1 text-lg tabular-nums text-gray-900">{{ card.value }}</div>
                                <div v-if="card.delta" class="mt-0.5 text-xs flex items-center gap-1 tabular-nums" :class="card.delta.direction === 'flat' ? 'text-gray-400' : card.delta.good ? 'text-green-700' : 'text-red-600'">
                                    <FontAwesomeIcon v-if="card.delta.direction !== 'flat'" :icon="card.delta.direction === 'up' ? 'fal fa-arrow-up' : 'fal fa-arrow-down'" fixed-width />
                                    <span>{{ card.delta.text }}</span>
                                    <span class="text-gray-400">{{ ctrans('vs previous') }}</span>
                                </div>
                                <div v-else-if="card.sub" class="mt-0.5 text-xs text-gray-400 tabular-nums">{{ card.sub }}</div>
                            </div>
                        </div>

                        <div v-if="hasOrganic" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2 rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <div class="flex items-baseline justify-between gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-800">{{ ctrans('Performance trend') }}</span>
                                        <span class="ml-2 text-xs text-gray-400">{{ performance.period_label }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: SERIES.reach }" />{{ ctrans('Reach') }}</span>
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: SERIES.engagements }" />{{ ctrans('Engagements') }}</span>
                                    </div>
                                </div>
                                <template v-if="hasTrend">
                                    <div class="h-44 mt-3"><Line :data="reachChart" :options="chartOptions(false)" /></div>
                                    <div class="h-28"><Line :data="engagementsChart" :options="chartOptions(true)" /></div>
                                </template>
                                <p v-else class="text-sm text-gray-400 py-16 text-center">{{ ctrans('No posts in this period') }}</p>
                            </div>

                            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <span class="text-sm font-medium text-gray-800">{{ ctrans('Platform contribution') }}</span>
                                <span class="ml-2 text-xs text-gray-400">{{ ctrans('share of reach') }}</span>
                                <div v-if="platformRows.length" class="mt-4 space-y-4">
                                    <div v-for="row in platformRows" :key="row.platform">
                                        <div class="flex items-baseline justify-between text-sm">
                                            <span class="flex items-center gap-2 text-gray-800">
                                                <span class="h-2.5 w-2.5 rounded-full" :style="{ background: platformColor(row.platform) }" />
                                                {{ row.label }}
                                            </span>
                                            <span class="tabular-nums text-gray-900">{{ short(row.reach) }}</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full rounded-full" :style="{ width: (row.share ?? 0) + '%', background: platformColor(row.platform) }" />
                                        </div>
                                        <div class="mt-1 flex justify-between text-xs text-gray-400 tabular-nums">
                                            <span>{{ pct(row.share) }} {{ ctrans('of total') }} · {{ count(row.posts) }} {{ ctrans('posts') }}</span>
                                            <span>{{ ctrans('ER') }} {{ pct(row.engagement_rate) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 py-16 text-center">{{ ctrans('No posts in this period') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                            <div v-if="hasOrganic" class="lg:col-span-3 rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm font-medium text-gray-800">{{ ctrans('Top organic content') }}</span>
                                    <button type="button" class="text-xs text-gray-500 hover:underline" @click="currentTab = 'organic'">{{ ctrans('See all') }}</button>
                                </div>
                                <table v-if="overviewPosts.length" class="mt-3 w-full text-sm">
                                    <thead class="text-xs text-gray-400 whitespace-nowrap">
                                        <tr class="text-left">
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Content') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Reach') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Engagements') }}</th>
                                            <th class="font-normal pb-2 text-right">{{ ctrans('ER') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="post in overviewPosts" :key="post.id">
                                            <td class="py-2 pr-3 max-w-xs">
                                                <a :href="post.permalink ?? undefined" :target="post.permalink ? '_blank' : undefined" rel="noopener" class="block truncate text-gray-800" :class="post.permalink ? 'hover:underline' : ''">{{ postTitle(post) }}</a>
                                                <div class="text-xs text-gray-400 flex items-center gap-1.5">
                                                    <FontAwesomeIcon :icon="formatStyle(post.type).icon" :style="{ color: formatStyle(post.type).color }" fixed-width />
                                                    {{ post.platform_label }} · {{ post.type_label }}
                                                </div>
                                            </td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(post.reach) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(post.engagements) }}</td>
                                            <td class="py-2 text-right"><span class="inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs tabular-nums text-gray-700">{{ pct(post.engagement_rate) }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p v-else class="text-sm text-gray-400 py-10 text-center">{{ ctrans('No posts in this period') }}</p>
                            </div>

                            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5" :class="hasOrganic ? 'lg:col-span-2' : 'lg:col-span-5'">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm font-medium text-gray-800">{{ ctrans('Paid summary') }}</span>
                                    <button type="button" class="text-xs text-gray-500 hover:underline" @click="currentTab = 'paid'">{{ ctrans('Campaigns') }}</button>
                                </div>
                                <div v-if="paidChannels.length" class="mt-3 divide-y divide-gray-100">
                                    <div v-for="channel in paidChannels" :key="channel.type" class="py-2 flex items-center justify-between gap-3 text-sm">
                                        <div class="min-w-0">
                                            <div class="text-gray-800 truncate">{{ channel.label }}</div>
                                            <div class="text-xs text-gray-400 tabular-nums">{{ short(channel.clicks) }} {{ ctrans('clicks') }} · {{ ctrans('CTR') }} {{ pct(channel.ctr, 2) }}</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="tabular-nums text-gray-900">{{ money(channel.spend) }}</div>
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs tabular-nums" :class="roasClass(channel.roas)">{{ ctrans('ROAS') }} {{ ratio(channel.roas) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 py-10 text-center">{{ ctrans('No paid spend in this period') }}</p>
                            </div>
                        </div>
                    </template>

                    <!-- Organic -->
                    <template v-else-if="currentTab === 'organic' && hasOrganic">
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            <div v-for="card in organicKpis" :key="card.key" class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                                <div class="text-xs text-gray-400">{{ card.label }}</div>
                                <div class="mt-1 text-lg tabular-nums text-gray-900">{{ card.value }}</div>
                                <div v-if="card.delta" class="mt-0.5 text-xs flex items-center gap-1 tabular-nums" :class="card.delta.direction === 'flat' ? 'text-gray-400' : card.delta.good ? 'text-green-700' : 'text-red-600'">
                                    <FontAwesomeIcon v-if="card.delta.direction !== 'flat'" :icon="card.delta.direction === 'up' ? 'fal fa-arrow-up' : 'fal fa-arrow-down'" fixed-width />
                                    <span>{{ card.delta.text }}</span>
                                    <span class="text-gray-400">{{ ctrans('vs previous') }}</span>
                                </div>
                                <div v-else-if="card.sub" class="mt-0.5 text-xs text-gray-400 tabular-nums">{{ card.sub }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2 rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <div class="flex items-baseline justify-between gap-4">
                                    <span class="text-sm font-medium text-gray-800">{{ ctrans('Reach and engagements') }}</span>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: SERIES.reach }" />{{ ctrans('Reach') }}</span>
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm" :style="{ background: SERIES.engagements }" />{{ ctrans('Engagements') }}</span>
                                    </div>
                                </div>
                                <template v-if="hasTrend">
                                    <div class="h-40 mt-3"><Line :data="reachChart" :options="chartOptions(false)" /></div>
                                    <div class="h-24"><Line :data="engagementsChart" :options="chartOptions(!hasFollowersTrend)" /></div>
                                </template>
                                <p v-else class="text-sm text-gray-400 py-16 text-center">{{ ctrans('No posts in this period') }}</p>
                                <template v-if="hasFollowersTrend">
                                    <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500"><span class="h-2 w-2 rounded-sm" :style="{ background: SERIES.followers }" />{{ ctrans('Followers') }}</div>
                                    <div class="h-24"><Line :data="followersChart" :options="chartOptions(true, false)" /></div>
                                </template>
                            </div>

                            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <span class="text-sm font-medium text-gray-800">{{ ctrans('Format comparison') }}</span>
                                <span class="ml-2 text-xs text-gray-400">{{ ctrans('average reach per post') }}</span>
                                <div v-if="formatRows.length" class="mt-4 space-y-4">
                                    <div v-for="row in formatRows" :key="row.type">
                                        <div class="flex items-baseline justify-between text-sm">
                                            <span class="flex items-center gap-2 text-gray-800">
                                                <FontAwesomeIcon :icon="formatStyle(row.type).icon" :style="{ color: formatStyle(row.type).color }" fixed-width />
                                                {{ row.label }}
                                                <span class="text-xs text-gray-400">{{ count(row.posts) }}</span>
                                            </span>
                                            <span class="tabular-nums text-gray-900">{{ short(row.average_reach) }}</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full rounded-full" :style="{ width: barWidth(row.average_reach, maxFormatReach), background: SERIES.reach }" />
                                        </div>
                                        <div class="mt-1 flex justify-between text-xs text-gray-400 tabular-nums">
                                            <span>{{ ctrans('ER') }} {{ pct(row.engagement_rate) }}</span>
                                            <span>{{ short(row.saves) }} {{ ctrans('saves') }} · {{ short(row.shares) }} {{ ctrans('shares') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 py-16 text-center">{{ ctrans('No posts in this period') }}</p>
                            </div>
                        </div>

                        <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">{{ ctrans('Top content') }}</span>
                                    <span class="ml-2 text-xs text-gray-400">{{ ctrans('ranked by') }}</span>
                                </div>
                                <SegmentedToggle v-model="postSort" :options="postSortOptions" />
                            </div>
                            <div v-if="visiblePosts.length" class="mt-3 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-gray-400 whitespace-nowrap">
                                        <tr class="text-left">
                                            <th class="font-normal pb-2 pr-3 w-8">#</th>
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Content') }}</th>
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Account') }}</th>
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Published') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Reach') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Engagements') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('ER') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Saves') }}</th>
                                            <th class="font-normal pb-2 text-right">{{ ctrans('Clicks') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="(post, index) in visiblePosts" :key="post.id" class="hover:bg-gray-50">
                                            <td class="py-2 pr-3 text-xs text-gray-400 tabular-nums">{{ index + 1 }}</td>
                                            <td class="py-2 pr-3 max-w-sm">
                                                <a :href="post.permalink ?? undefined" :target="post.permalink ? '_blank' : undefined" rel="noopener" class="flex items-center gap-1.5 text-gray-800" :class="post.permalink ? 'hover:underline' : ''">
                                                    <span class="truncate">{{ postTitle(post) }}</span>
                                                    <FontAwesomeIcon v-if="post.permalink" icon="fal fa-external-link" class="text-gray-300 text-xs shrink-0" fixed-width />
                                                </a>
                                                <div class="text-xs text-gray-400 flex items-center gap-1.5">
                                                    <FontAwesomeIcon :icon="formatStyle(post.type).icon" :style="{ color: formatStyle(post.type).color }" fixed-width />
                                                    {{ post.platform_label }} · {{ post.type_label }}
                                                </div>
                                            </td>
                                            <td class="py-2 pr-3 text-xs text-gray-500 whitespace-nowrap">
                                                <div>{{ post.account_name }}</div>
                                                <div class="text-gray-400">{{ post.shop_name }}</div>
                                            </td>
                                            <td class="py-2 pr-3 text-xs text-gray-500 whitespace-nowrap">{{ day(post.published_at) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(post.reach) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(post.engagements) }}</td>
                                            <td class="py-2 pr-3 text-right"><span class="inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs tabular-nums text-gray-700">{{ pct(post.engagement_rate) }}</span></td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(post.saves) }}</td>
                                            <td class="py-2 text-right tabular-nums">{{ short(post.link_clicks) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="sortedPosts.length > 10" class="mt-3 text-center">
                                    <button type="button" class="text-xs text-gray-500 hover:underline" @click="showAllPosts = !showAllPosts">
                                        {{ showAllPosts ? ctrans('Show top 10') : ctrans('Show :count more', { count: String(sortedPosts.length - 10) }) }}
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 py-10 text-center">{{ ctrans('No posts in this period') }}</p>
                        </div>

                        <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                            <span class="text-sm font-medium text-gray-800">{{ ctrans('Accounts') }}</span>
                            <span class="ml-2 text-xs text-gray-400">{{ ctrans('brand × platform × market') }}</span>
                            <div v-if="accounts.length" class="mt-3 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-gray-400 whitespace-nowrap">
                                        <tr class="text-left">
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Account') }}</th>
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Brand') }}</th>
                                            <th class="font-normal pb-2 pr-3">{{ ctrans('Market') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Followers') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Posts') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Reach') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Engagements') }}</th>
                                            <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Clicks') }}</th>
                                            <th class="font-normal pb-2 text-right">{{ ctrans('ER') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50">
                                            <td class="py-2 pr-3">
                                                <div class="flex items-center gap-2 text-gray-800">
                                                    <span class="h-2.5 w-2.5 rounded-full shrink-0" :style="{ background: platformColor(account.platform) }" />
                                                    {{ account.name }}
                                                </div>
                                                <div class="text-xs text-gray-400 pl-[18px]">{{ account.platform_label }}<span v-if="account.username"> · @{{ account.username }}</span></div>
                                            </td>
                                            <td class="py-2 pr-3 text-gray-600">{{ account.shop_name }}</td>
                                            <td class="py-2 pr-3 text-gray-600 whitespace-nowrap">{{ account.country_code }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">
                                                <span v-tooltip="account.followers_at ? ctrans('at') + ' ' + day(account.followers_at) : undefined">{{ account.followers === null ? '–' : short(account.followers) }}</span>
                                            </td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ count(account.posts) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(account.reach) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(account.engagements) }}</td>
                                            <td class="py-2 pr-3 text-right tabular-nums">{{ short(account.link_clicks) }}</td>
                                            <td class="py-2 text-right"><span class="inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs tabular-nums text-gray-700">{{ pct(account.engagement_rate) }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-sm text-gray-400 py-10 text-center">{{ ctrans('No accounts match these filters') }}</p>
                        </div>
                    </template>

                    <!-- Paid -->
                    <template v-else-if="currentTab === 'paid'">
                        <p class="text-xs text-gray-500 max-w-3xl">
                            {{ ctrans('Paid figures come from the nightly Meta Ads sync and the sales attributed to those ads. ROAS and CPA use attributed revenue and orders, so they move as invoices are raised.') }}
                            <span class="text-gray-400">{{ ctrans('All figures in') }} {{ currency }}.</span>
                        </p>

                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            <div v-for="card in paidKpis" :key="card.key" class="rounded-xl ring-1 ring-gray-200 bg-white p-4">
                                <div class="text-xs text-gray-400 flex items-center gap-1">
                                    {{ card.label }}
                                    <FontAwesomeIcon v-if="card.help" v-tooltip="card.help" icon="fal fa-info-circle" class="text-gray-300 cursor-help" fixed-width />
                                </div>
                                <div class="mt-1 text-lg tabular-nums text-gray-900">{{ card.value }}</div>
                                <div v-if="card.delta" class="mt-0.5 text-xs flex items-center gap-1 tabular-nums" :class="card.delta.direction === 'flat' ? 'text-gray-400' : card.delta.good ? 'text-green-700' : 'text-red-600'">
                                    <FontAwesomeIcon v-if="card.delta.direction !== 'flat'" :icon="card.delta.direction === 'up' ? 'fal fa-arrow-up' : 'fal fa-arrow-down'" fixed-width />
                                    <span>{{ card.delta.text }}</span>
                                    <span class="text-gray-400">{{ ctrans('vs previous') }}</span>
                                </div>
                                <div v-else-if="card.sub" class="mt-0.5 text-xs text-gray-400 tabular-nums">{{ card.sub }}</div>
                            </div>
                        </div>

                        <div v-if="hasPaid" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <span class="text-sm font-medium text-gray-800">{{ ctrans('Channel split') }}</span>
                                <span class="ml-2 text-xs text-gray-400">{{ ctrans('spend') }}</span>
                                <div class="mt-4 space-y-4">
                                    <div v-for="channel in paidChannels" :key="channel.type">
                                        <div class="flex items-baseline justify-between text-sm">
                                            <span class="text-gray-800">{{ channel.label }}</span>
                                            <span class="tabular-nums text-gray-900">{{ money(channel.spend) }}</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-gray-700" :style="{ width: barWidth(channel.spend, maxChannelSpend) }" />
                                        </div>
                                        <div class="mt-1 flex justify-between text-xs text-gray-400 tabular-nums">
                                            <span>{{ short(channel.impressions) }} {{ ctrans('impressions') }} · {{ short(channel.clicks) }} {{ ctrans('clicks') }} · {{ ctrans('CTR') }} {{ pct(channel.ctr, 2) }}</span>
                                            <span>{{ ctrans('ROAS') }} {{ ratio(channel.roas) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2 rounded-xl ring-1 ring-gray-200 bg-white p-5">
                                <span class="text-sm font-medium text-gray-800">{{ ctrans('Paid campaign performance') }}</span>
                                <div v-if="campaigns.length" class="mt-3 overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="text-xs text-gray-400 whitespace-nowrap">
                                            <tr class="text-left">
                                                <th class="font-normal pb-2 pr-3">{{ ctrans('Campaign') }}</th>
                                                <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Spend') }}</th>
                                                <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Clicks') }}</th>
                                                <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('CTR') }}</th>
                                                <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('Orders') }}</th>
                                                <th class="font-normal pb-2 pr-3 text-right">{{ ctrans('CPA') }}</th>
                                                <th class="font-normal pb-2 text-right">{{ ctrans('ROAS') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-for="campaign in campaigns" :key="campaign.channel + campaign.name" class="hover:bg-gray-50">
                                                <td class="py-2 pr-3 max-w-xs">
                                                    <div class="truncate text-gray-800">{{ campaign.name }}</div>
                                                    <div class="text-xs text-gray-400">{{ campaign.channel }}<span v-if="campaign.owner"> · {{ campaign.owner }}</span></div>
                                                </td>
                                                <td class="py-2 pr-3 text-right tabular-nums">{{ money(campaign.spend) }}</td>
                                                <td class="py-2 pr-3 text-right tabular-nums">{{ short(campaign.clicks) }}</td>
                                                <td class="py-2 pr-3 text-right tabular-nums">{{ pct(campaign.ctr, 2) }}</td>
                                                <td class="py-2 pr-3 text-right tabular-nums">{{ count(campaign.orders) }}</td>
                                                <td class="py-2 pr-3 text-right tabular-nums">{{ money(campaign.cpa) }}</td>
                                                <td class="py-2 text-right"><span class="inline-block rounded-full px-2 py-0.5 text-xs tabular-nums" :class="roasClass(campaign.roas)">{{ ratio(campaign.roas) }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-sm text-gray-400 py-10 text-center">{{ ctrans('No campaigns in this period') }}</p>
                            </div>
                        </div>
                        <div v-else class="rounded-xl ring-1 ring-gray-200 bg-white p-6 text-sm text-gray-500 text-center">
                            {{ ctrans('No paid spend in this period. Spend appears here the morning after the Meta Ads sync has run for a connected ad account.') }}
                        </div>
                    </template>

                    <!-- Uploads -->
                    <template v-else-if="currentTab === 'uploads'">
                        <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="text-sm text-gray-600 max-w-3xl">
                                <div class="font-medium text-gray-800">{{ ctrans('Import platform exports') }}</div>
                                <p class="mt-1 text-xs text-gray-500">{{ uploadInformation }}</p>
                            </div>
                            <Button v-if="uploadShopOptions.length" icon="fal fa-upload" :label="ctrans('Import export')" type="tertiary" class="shrink-0" @click="startUpload" />
                        </div>
                        <Deferred data="uploads">
                            <template #fallback>
                                <div class="h-40 animate-pulse rounded-xl bg-gray-100 ring-1 ring-gray-200" />
                            </template>
                            <UploadReports :data="uploads" tab="uploads" />
                        </Deferred>
                    </template>
                </div>
            </div>
        </Deferred>
    </div>

    <Dialog v-model:visible="isShopPickerOpen" modal :header="ctrans('Which brand is this export for?')" :style="{ width: '26rem' }">
        <p class="text-xs text-gray-500">{{ ctrans('Posts and daily figures are stored against the brand they belong to, so the dashboard can compare brands and markets.') }}</p>
        <Select v-model="uploadShop" :options="uploadShopOptions" optionLabel="name" :placeholder="ctrans('Choose a brand')" class="mt-3 w-full" />
        <div class="mt-4 flex justify-end gap-2">
            <Button :label="ctrans('Cancel')" icon="fal fa-times" type="tertiary" @click="isShopPickerOpen = false">
                <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width /></template>
            </Button>
            <Button :label="ctrans('Continue')" icon="fal fa-upload" type="tertiary" :disabled="!uploadShop" @click="continueUpload" />
        </div>
    </Dialog>

    <UploadExcel
        v-if="uploadSpreadsheet"
        v-model="isUploadOpen"
        scope="SocialMediaPost"
        :title="{ label: ctrans('Import social media export') + (uploadShop ? ' · ' + uploadShop.name : ''), information: uploadInformation }"
        :progressDescription="ctrans('Importing social media insights')"
        :upload_spreadsheet="uploadSpreadsheet"
        :propsRefreshAfterFinish="['performance', 'uploads']"
    />
  </div>
</template>

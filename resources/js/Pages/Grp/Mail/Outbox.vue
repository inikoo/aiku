<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import Fieldset from 'primevue/fieldset';
import Avatar from 'primevue/avatar';
import PureTimeline from '@/Components/Pure/PureTimeline.vue'

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, ref } from 'vue'
import type { Component } from 'vue'
import Timeline from '@/Components/Utils/Timeline.vue'

import beePluginJsonExample from "@/Components/CMS/Website/Outboxes/Unlayer/beePluginJsonExample"

import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import { library } from "@fortawesome/fontawesome-svg-core";
import { faInboxOut, faShoppingCart } from "@fal";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faUser } from '@fas';
library.add(faInboxOut)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    history?: {}
    showcase?: any
    imagesUploadRoute: routeType
    updateRoute: routeType
    emailTemplate: routeType
    publishRoute: routeType
    loadRoute: routeType
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    console.log(currentTab.value)
    const components: Component = {
        history: TableHistories,
    }

    return components[currentTab.value]
})

const events = [
    {
        label: "Mailshot Created",
        tooltip: "mailshot_created",
        key: "mailshot_created",
        icon: "fal fa-seedling",
        current: true,
        timestamp: null
    },
    {
        label: "Mailshot Composed",
        tooltip: "mailshot_Composed",
        key: "mailshot_Composed",
        icon: "fal fa-share",
        current: true,
        timestamp: null
    },
    {
        label: "Start End",
        tooltip: "start_end",
        key: "start_end",
        icon: "fal fa-share",
        current: true,
        timestamp: null
    },
    {
        label: "Sent",
        tooltip: "Sent",
        key: "Sent",
        icon: "fal fa-check",
        current: false,
        timestamp: null
    },
]

const data = [
    { label: 'Recipient', value: 99, icon: faUser, class: 'from-blue-500  to-sky-300' },
    { label: 'Bounce', value: 99, icon: faUser, class: 'from-blue-500  to-sky-300' },
    { label: 'Delivered', value: "99/100", icon: faUser, class: 'from-blue-500  to-sky-300' },
    { label: 'Opened', value: 99, icon: faUser, class: 'from-blue-500  to-sky-300' },
    { label: 'Clicked', value: 99, icon: faUser, class: 'from-green-500  to-green-300' },
    { label: 'Spam', value: 99, icon: faUser, class: 'from-orange-500  to-orange-300' },
    { label: 'Unsubscribed', value: 99, icon: faUser, class: 'from-red-500  to-red-300' },
]

</script>



<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />

    <div class="card p-4">
        <div class="col-span-2 w-full pb-4 border-b border-gray-300 mb-8">
            <Timeline :options="events" :slidesPerView="4" color="#6366f1" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-8 gap-4">
            <!-- Data Grid -->
            <div class="md:col-span-8 grid sm:grid-cols-1 md:grid-cols-7 gap-4 h-auto mb-3">
                <div v-for="item in data" :key="item.label" :class="item.class"
                    class="bg-gradient-to-tr text-white flex flex-col justify-between px-6 py-2 rounded-lg shadow-lg sm:h-auto">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ item.label }}</div>
                        </div>
                        <div class="rounded-full bg-white/20 p-2">
                            <FontAwesomeIcon :icon="item.icon" class="text-xl" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ item.value }}</div>
                        <div class="text-sm text-white/80">Updated 5 minutes ago</div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="md:col-span-8">
                <div
                    class="p-6 border rounded-lg bg-white shadow-lg hover:shadow-2xl transition-transform duration-300">
                    <div v-html="beePluginJsonExample" />
                </div>
            </div>
        </div>
    </div>

</template>



<style lang="scss" scoped>
.card {
    padding: 1rem;
    border-radius: 8px;

    @media (max-width: 768px) {
        padding: 0.5rem;
    }
}

.grid-cols-7 {
    display: grid;
    grid-template-columns: repeat(7, 1fr);

    @media (max-width: 768px) {
        grid-template-columns: repeat(2, 1fr);
    }
}

.text-xl {
    font-size: 1.25rem;

    @media (max-width: 640px) {
        font-size: 1rem;
    }
}
</style>

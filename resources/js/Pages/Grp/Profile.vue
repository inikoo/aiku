<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import LoadingText from "@/Components/Utils/LoadingText.vue"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import { computed, defineAsyncComponent, inject, onMounted, ref, watch } from 'vue'
import type { Component } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TableTimesheets from "@/Components/Tables/Grp/Org/HumanResources/TableTimesheets.vue"
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue"
import ProfileShowcase from "@/Components/Profile/ProfileShowcase.vue"
// import EditProfile from "@/Pages/Grp/EditProfile.vue"

import axios from 'axios'
import { trans } from 'laravel-vue-i18n'
import { notify } from '@kyvg/vue3-notification'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faIdCard } from '@fal'
import { faInfoCircle } from '@fas'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faIdCard, faSpinnerThird, faInfoCircle)

const EditProfile = defineAsyncComponent(() => import("@/Pages/Grp/EditProfile.vue"))


const props = defineProps<{
    // title: string,
    // pageHead: TSPageHeading
    // tabs?: TSTabs
    history?: {}
    timesheets?: {}
    visit_logs?: {}
    showcase?: {}

}>()


const layout = inject('layout', layoutStructure)


// Section: Fetch Tab data
const currentTab = ref('showcase')
const component = computed(() => {
    const components: Component = {
        showcase: ProfileShowcase,
        visit_logs: TableUserRequestLogs,
        timesheets: TableTimesheets,
        history: TableHistories,
    }

    return components[currentTab.value]
})
const handleTabUpdate = (newTabSlug: string) => {
    if (newTabSlug === currentTab.value) {
        return
    }

    fetchTabData(newTabSlug)
}
const isTabLoading = ref(false)
const dataTab = ref(null)
const fetchTabData = async (tabName: string) => {
    isTabLoading.value = true
    let routeName = ''

    switch (tabName) {
        case 'showcase':
            routeName = 'grp.profile.showcase.show'
            break
        case 'timesheets':
            routeName = 'grp.profile.timesheets.index'
            break
        case 'histories':
            routeName = 'grp.profile.history.index'
            break
        case 'visit_logs':
            routeName = 'grp.profile.visit-logs.index'
            break
    }

    try {
        const { data } = await axios.get(
            route('grp.profile.show'),
        )
        dataTab.value = data.data
        currentTab.value = tabName
        // console.log('response', dataTab.value)
    } catch (error: any) {
        dataTab.value = null
        notify({
            title: trans('Something went wrong.'),
            text: trans('Failed to show this tab.'),
            type: 'error',
        })
    }

    isTabLoading.value = false
}

// Section: fetch PageHead and Tabs
const dataProfile = ref<{ pageHead: TSPageHeading, tabs: TSTabs } | null>(null)
const fetchPageHead = async () => {
    try {
        const { data } = await axios.get(
            route('grp.profile.page-head-tabs.show'),
        )
        dataProfile.value = data
        // console.log('response pageHead', data)
    } catch (error: any) {
        dataProfile.value = null
        notify({
            title: trans('Something went wrong.'),
            text: trans('Failed to show Profile page.'),
            type: 'error',
        })
    }
}

onMounted(() => {
    // console.log("On mounted currentTab")
    fetchPageHead()
    fetchTabData(currentTab.value)
})

</script>


<template>

    <Head :title="trans('Profile')" />
    <PageHeading v-if="dataProfile?.pageHead" :data="dataProfile?.pageHead">
        <template #button-edit-profile="{ action }">
            <Button @click="() => layout.stackedComponents.push({ component: EditProfile })" :label="action.action.label"
                :style="action.action.style" />
        </template>
    </PageHeading>

    <template v-if="dataProfile?.tabs?.navigation">
        <Tabs :current="currentTab" :navigation="dataProfile?.tabs?.navigation"
            @update:tab="(tabSlug: string) => handleTabUpdate(tabSlug)" />

        <!-- Loading: main content -->
        <div v-if="isTabLoading" class="pt-32 w-full flex justify-center">
            <LoadingIcon size="2x" :key="32"/>
        </div>

        <component v-else-if="dataTab" :is="component" :data="dataTab" :tab="currentTab" />

        <div v-else class="h-full w-full flex items-center justify-center text-gray-400 italic">
            {{ trans('No data to shown.') }}
        </div>
    </template>

    <!-- Loading: Navigation -->
    <div v-else class="pt-8 w-full flex items-center justify-center">
        <LoadingIcon size="2x"/>
    </div>
</template>
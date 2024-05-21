<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, onMounted, ref } from 'vue'
import type { Component } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TableTimesheets from "@/Components/Tables/Grp/Org/HumanResources/TableTimesheets.vue"
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue"
import ProfileShowcase from "@/Components/Profile/ProfileShowcase.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faIdCard } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
import axios from 'axios'
library.add(faIdCard, faSpinnerThird)


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs?: TSTabs
    history?: {}
    timesheets?: {}
    visit_logs?: {}
    showcase?: {}

}>()

const currentTab = ref(props.tabs?.current || 'showcase')
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        showcase: ProfileShowcase,
        visit_logs: TableUserRequestLogs,
        timesheets: TableTimesheets,
        history: TableHistories,
    }

    return components[currentTab.value]

})

const isLoading = ref(false)
const dataProfile = ref(null)
const fetchProfileData = async () => {
    isLoading.value = true
    try {
        const { data } = await axios.get(
            route('grp.profile.show'),
        )
        dataProfile.value = data.data
        console.log('response', dataProfile.value)
    } catch (error: any) {

    }
    isLoading.value = false
}

onMounted(() => {
    fetchProfileData()
})

</script>


<template>

    <!-- <Head :title="capitalize(title)" /> -->
    <!-- <PageHeading :data="pageHead" /> -->
    <Tabs :current="currentTab" :navigation="tabs?.navigation" @update:tab="handleTabUpdate" />
    <div v-if="isLoading" class="h-full w-full flex items-center justify-center">
        <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin' size="4x" fixed-width aria-hidden='true' />
    </div>
    <component v-else :is="ProfileShowcase" :data="dataProfile" tab="showcase" />
</template>
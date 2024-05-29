<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 07 Sept 2022 23:27:32 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import TableUsers from "@/Components/Tables/Grp/SysAdmin/TableUsers.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref } from "vue"
import type { Component } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import { faRoad, faTerminal } from '@fal'
import TableUserRequestLogs from "@/Components/Tables/Grp/SysAdmin/TableUserRequestLogs.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { capitalize } from "@/Composables/capitalize"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"

library.add(faRoad, faTerminal)
const props = defineProps<{
    // pageHead: {}
    tabs: {
        current: string
        navigation: {}
    },
    title: string
    users?: {}
    users_requests?: {}
    users_histories: {}
}>()


const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {
    const components: Component = {
        users: TableUsers,
        users_requests: TableUserRequestLogs,
        users_histories: TableHistories
    }

    return components[currentTab.value]
})

</script>

<template>
    <Head :title="capitalize(title)" />
    <!-- <PageHeading :data="pageHead"></PageHeading> -->
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :tab="currentTab" :data="props[currentTab as keyof typeof props]"></component>
</template>

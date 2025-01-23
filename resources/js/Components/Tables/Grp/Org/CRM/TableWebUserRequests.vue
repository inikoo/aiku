<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Feb 2024 19:17:38 CEST Time, Plane Madrid - Mexico City
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WebUser } from "@/types/web-user";
import Icon from '@/Components/Icon.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { useFormatTime } from '@/Composables/useFormatTime'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faDesktopAlt, faQuestionCircle } from '@far'
import { faWindows, faEdge, faFirefoxBrowser, faChrome, faApple } from '@fortawesome/free-brands-svg-icons'
import { faServer,faMobileAlt } from '@fas'

library.add(faQuestionCircle, faServer, faChrome, faApple, faDesktopAlt, faWindows, faMobileAlt, faEdge, faFirefoxBrowser)


defineProps<{
    data: object,
    tab?: string
}>();

const formatDate = (dateIso: Date) => {
    const date = new Date(dateIso)
    return date.toLocaleString()
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(date)="{ item: webUserRequest }">
            {{ formatDate(webUserRequest.date) }}
        </template>
        <template #cell(user_agent)="{ item: webUserRequest }">
            <div class="flex flex-row space-x-2">
                <Icon v-if="webUserRequest.user_agent?.[0]" :data="webUserRequest.user_agent[0]"></Icon>
                <Icon v-if="webUserRequest.user_agent?.[1]" :data="webUserRequest.user_agent[1]"></Icon>
                <Icon v-if="webUserRequest.user_agent?.[2]" :data="webUserRequest.user_agent[2]"></Icon>
            </div>
        </template>
        <template #cell(location)="{ item: webUserRequest }">

            
            <AddressLocation v-if="webUserRequest?.location != null && !webUserRequest.server" :data="webUserRequest.location" />
            <FontAwesomeIcon v-else-if="webUserRequest?.location != null && webUserRequest.server" :icon="['fas', 'server']" v-tooltip="'server'" fixed-width aria-hidden="true"/>
            <FontAwesomeIcon v-else :icon="['far', 'fa-question-circle']" v-tooltip="'Unknown location'" fixed-width aria-hidden="true" />
        
        </template>

    </Table>


</template>



<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import ShowcaseStats from '@/Components/ShowcaseStats.vue'
import ShowcaseContactCard from "@/Components/ShowcaseContactCard.vue"
import BoxDisplay from "@/Components/DataDisplay/BoxDisplay.vue"
import { Agent } from '@/types/Grp/Agent'

const props = defineProps<{
    data: {
        contactCard: Agent
        stats: {
            label: string
            count: number
            description?: string
            full?: boolean
        }[]
    }

}>()
</script>

<template>
    <div class="grid text-gray-600 gap-y-3 md:gap-y-0 md:grid-cols-2 px-4 pt-4 gap-x-6">
        <!-- Section 1 -->
        <div class="">
            <ShowcaseContactCard :data="data.contactCard" />
        </div>

        <!-- Section 2: Statistic -->
        <div class="">
            <BoxDisplay :data="data.stats" />
            <!-- <ShowcaseStats :data="data.stats" /> -->
        </div>


    </div>
</template>

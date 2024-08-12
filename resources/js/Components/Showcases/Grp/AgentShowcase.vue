<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import ShowcaseStats from '@/Components/ShowcaseStats.vue'
import ShowcaseContactCard from "@/Components/ShowcaseContactCard.vue"
import { Agent } from '@/types/Grp/Agent'

const props = defineProps<{
    data: {
        agent: Agent
        stats: {
            label: string
            value: number
        }[]
    }

}>()
</script>

<template>
    <div class="grid text-gray-600  grid-flow-col grid-cols-2 px-4 pt-4 gap-x-6">
        <!-- Section 1 -->
        <div class="">
            <ShowcaseContactCard :data="data.agent" />
        </div>

        <!-- Section 2: Statistic -->
        <div class="">
            <ShowcaseStats :data="data.stats" />
        </div>


    </div>
</template>

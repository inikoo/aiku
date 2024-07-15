
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 14 Sep 2023 00:06:48 Malaysia Time, Pantai Lembeng, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'

const props = defineProps<{
    data: {
        slug: string
        state: string
        status: string
        created_at: string
        updated_at: string
    }
}>()


</script>

<template>
    <div class="px-6 py-24 sm:py-20 lg:px-8">
        <template v-if="data.layout.web_blocks?.length">
            <div v-for="(activityItem, activityItemIdx) in data.layout.web_blocks"
                :key="'block' + activityItem.id"
                class="w-full">
                <!-- ==== {{ activityItem.web_block.layout.data.fieldValue }} -->
                <component
                    :is="getComponent(activityItem.web_block.layout.data.component)"
                    :key="activityItemIdx"
                    v-model="activityItem.web_block.layout.data.fieldValue"
                    :isEditable="false"
                    v-bind="activityItem.web_block.layout.data.fieldValue"
                />
                <!-- <pre>{{ activityItem.web_block.layout.data.fieldValue }}</pre> -->
            </div>
        </template>

        <div v-else class="text-center text-2xl sm:text-4xl font-bold text-gray-400 mt-16">
            This page have no data
        </div>

        <pre>{{ data.layout.web_blocks }}</pre>
    </div>
</template>

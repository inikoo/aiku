
<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 14 Sep 2023 00:06:48 Malaysia Time, Pantai Lembeng, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getIrisComponent } from '@/Composables/getIrisComponents'
import BrowserView from '@/Components/Pure/BrowserView.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHome, faSignIn } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faHome, faSignIn)

const props = defineProps<{
    data: {
        slug: string
        state: string
        status: string
        created_at: string
        updated_at: string
        domain: string
    }
}>()

</script>

<template>
    <div class="px-6 py-24 sm:py-20 lg:px-8">
        <div class="grid grid-cols-2">
            <div>
                <pre>{{ data }}</pre>
            </div>

            <BrowserView
                :tab="{
                    icon: data.typeIcon,
                    label: data.code
                }"
                :url="{
                    domain: data.domain,
                    page: data.url
                }"

            >
                <template #page v-if="data.layout.web_blocks?.length">
                    <template v-if="data.layout.web_blocks?.length">
                        <div class="px-10">
                            <div v-for="(activityItem, activityItemIdx) in data.layout.web_blocks"
                                :key="'block' + activityItem.id"
                                class="w-full"
                            >
                                <component
                                    v-if="activityItem.web_block?.layout?.data?.fieldValue"
                                    :is="getIrisComponent(activityItem.type)"
                                    :key="activityItemIdx"
                                    :properties="activityItem?.web_block?.layout?.properties"
                                    :isEditable="false"
                                    :fieldValue="activityItem.web_block.layout.data.fieldValue"
                                />
                            </div>
                        </div>
                    </template>
                    <div v-else class="text-center text-2xl sm:text-4xl font-bold text-gray-400 mt-16">
                        This page have no data
                    </div>
                </template>
            </BrowserView>
        </div>

    </div>
</template>

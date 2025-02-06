<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 12:36:21 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import Button from '@/Components/Elements/Buttons/Button.vue'
import { useCopyText } from '@/Composables/useCopyText'
import { faExternalLink, faLink, faPencil } from '@fal'
import { ref } from 'vue'
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faExternalLink, faLink)

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    data: {
        slug: string
        url: string
        domain: string
        state: string
        status: string
        created_at: string
        updated_at: string
    }
}>()


const links = ref([
    { label: "Edit Header", url: "/", icon: faPencil },
    { label: "Edit Menu", url: "/about", icon: faPencil },
    { label: "Edit Webpages", url: "/services", icon: faPencil },
    { label: "Edit Footer", url: "/contact", icon: faPencil }
]);

</script>

<template>
    <!-- <div class="px-6 py-24 sm:py-20 lg:px-8">
        {{ data.url }}
        <a :href="data.url" target="_blank">
            <FontAwesomeIcon class="ml-3" icon="fal fa-external-link" aria-hidden="true" />
        </a>
    </div> -->

    <!-- Box: Url (copy button) -->
    <div class="px-6 py-12 lg:px-8">
        <div class="bg-white border border-gray-300 flex items-center justify-between gap-x-3 rounded-md md:w-96">
            <a :href="data.url" target="_blank" class="pl-4 md:pl-5 inline-block text-xxs md:text-base text-gray-400">{{
                data.url }}</a>
            <Button :style="'tertiary'" class="" @click="useCopyText(data.url)"
                v-tooltip="trans('Copy url to clipboard')">
                <FontAwesomeIcon icon='fal fa-link' class='text-gray-500' aria-hidden='true' />
            </Button>
        </div>
        <div class="px-6 py-12 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(item, index) in links" :key="index">
           
                <Button full  :icon="item.icon" :label="item.label">
                </Button>
            </div>
        </div>
    </div>

</template>

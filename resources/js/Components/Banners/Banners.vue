<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 02 Oct 2023 03:20:36 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faGlobe,faImage } from '@fal'
import TableBanners from "@/Components/Tables/TableBanners.vue"
import FirstBannerWidget from '@/Components/EmptyState/FirstBannerWidget.vue'


library.add(faGlobe,faImage)
1995

const props = defineProps<{
    pageHead: object
    title: string
    data: object
    firstBanner?: {
        text: string
        createRoute: {
            name: string
            parameters?: any[]
        }
    }
}>()

const isOpen = ref(false)
</script>

<template layout="CustomerApp">
    <!-- <pre>{{ props }}</pre> -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <FirstBannerWidget v-if="firstBanner" :data="firstBanner" />
    <TableBanners v-else :data="data" />
</template>


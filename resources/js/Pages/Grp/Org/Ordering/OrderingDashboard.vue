<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 27 Jan 2025 20:05:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2025, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faTimesCircle } from "@fas"

import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as PageHeadingTS } from '@/types/PageHeading'


library.add(faCheckCircle, faTimesCircle )

defineProps<{
    pageHead: PageHeadingTS
    tabs: {
        current: string
        navigation: {}
    },
    title: string
}>()





</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />




</template>


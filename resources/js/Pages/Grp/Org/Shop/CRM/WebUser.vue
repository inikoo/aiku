<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 24 Nov 2022 10:39:51 Central Indonesia Time, Ubud, Bali, Indonesia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faGlobe } from '@fal'
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'

library.add(faGlobe)

// const props = defineProps(['title', 'pageHead', 'customer']);

const props = defineProps<{
    title: string
    pageHead: TSPageHeading
}>()


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <div>
        Web user data
    </div>
</template>

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:45:56 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import TabsBox from "@/Components/Navigation/TabsBox.vue"
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'

const props = defineProps<{
  title: string
  pageHead: PageHeadingTypes
  flatTreeMaps: Navigation[]
  tabs_box: {}
}>()

import { library } from '@fortawesome/fontawesome-svg-core'
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt } from '@fal'
import { Navigation } from '@/types/Navigation'
import { ref } from 'vue'
import { useTabChange } from '@/Composables/tab-change'

library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt)


let currentTab = ref('showcase')
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab);

</script>

<template>

  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead"></PageHeading>
  <TabsBox :tabs_box="tabs_box" :current="currentTab" @update:tab="handleTabUpdate" />


</template>

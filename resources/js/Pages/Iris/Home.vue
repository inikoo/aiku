<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 16:53:19 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faPlus, faMinus } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import LayoutIris from '@/Layouts/Iris.vue'
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'

import { usePage } from '@inertiajs/vue3'

// import "https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap"

const props = defineProps<{
  /*    data: any,
     header : any, */
  blocks: any,
}>()


defineOptions({ layout: LayoutIris })
library.add(faCheck, faPlus, faMinus)

console.log('DDD',props.blocks.web_blocks)

</script>

<template>

  <Head title="Warehouse Solution" />

  <div class="bg-white pb-20">
    <template v-if="props.blocks?.web_blocks?.length">
      <div v-for="(activityItem, activityItemIdx) in props.blocks.web_blocks" :key="'block' + activityItem.id"
        class="w-full">

        <component :is="getComponent(activityItem.type)" :key="activityItemIdx"
          v-model="activityItem.web_block.layout.fieldValue" :isEditable="false"
          v-bind="activityItem.web_block.layout.fieldValue" />

      </div>
    </template>
    <div v-else class="text-center text-2xl sm:text-4xl font-bold text-gray-400 mt-16">
      This page have no data
    </div>

  </div>

</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap');
</style>

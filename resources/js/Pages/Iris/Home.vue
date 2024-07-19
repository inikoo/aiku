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
    blocks : any,
}>()


defineOptions({ layout: LayoutIris })
library.add(faCheck, faPlus, faMinus)



const people = [
  {
    name: 'South Yorkshire - Heart of the UK',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830486',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: '100 000 Square Foot',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830487',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Pallet & Parcel Delivery across UK',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830488',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Storage Solutions',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849917',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Pick & Pack',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849918',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Rework & Repack  ',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849919',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  // More people...
]


</script>

<template>
    <Head title="Warehouse Solution" />
<!-- <pre>{{ blocks.web_blocks[0].web_block.layout.data.component }}</pre> -->

    <div class="bg-white pb-20">
  
        <template v-if="props.blocks?.web_blocks?.length">
            <div v-for="(activityItem, activityItemIdx) in props.blocks.web_blocks"
                :key="'block' + activityItem.id"
                class="w-full">
               
                <component
                    :is="getComponent(activityItem.web_block.layout.data.component)"
                    :key="activityItemIdx"
                    v-model="activityItem.web_block.layout.data.fieldValue"
                    :isEditable="false"
                    v-bind="activityItem.web_block.layout.data.fieldValue"
                />
            
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

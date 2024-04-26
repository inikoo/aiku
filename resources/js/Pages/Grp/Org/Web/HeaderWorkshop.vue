<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, reactive, watch } from "vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from "@headlessui/vue"
import Footer from "@/Components/CMS/Footer/index.vue"
import { faHandPointer, faHandRock, faPlus, faAlignJustify, faList, faInfoCircle } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { v4 as uuidv4 } from "uuid"
import HyperlinkTools from "@/Components/CMS/Fields/Hyperlinktools.vue"
import { get, set } from "lodash"
import HyperInfoTools from "@/Components/CMS/Fields/InfoFieldTools.vue"
library.add(faHandPointer, faHandRock, faPlus, faAlignJustify, faList, faInfoCircle)
import { notify } from "@kyvg/vue3-notification"

const props = defineProps < {
    data: Object
	imagesUploadRoute: Object
} > ()


const blocks = [
    {
        title: 'block1',
        image: " https://templatemo.com/screenshots-720/template-579-cyborg-gaming.jpg"
    },
    {
        title: 'block2',
        image: "https://templatemo.com/screenshots-720/template-579-cyborg-gaming.jpg"
    },
    {
        title: 'block3',
        image: "https://templatemo.com/screenshots-720/template-579-cyborg-gaming.jpg"
    },
    {
        title: 'block4',
        image: "https://templatemo.com/screenshots-720/template-579-cyborg-gaming.jpg"
    },
]

</script>

<template>
<div class="bg-white flex border border-black">
    <div class="w-[250px] p-6 overflow-y-auto overflow-x-hidden h-[46rem] w-[30%] border-r border-black">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Blocks</h2>
        </div>
        <!-- Tampilkan gambar-gambar -->
        <div class="mt-4 grid grid-cols-1 gap-4">
            <div v-for="item of blocks" :key="item.title">
                <img :src="item.image" alt="Block Image" class="w-full h-[300px]" />
                <p class="text-sm text-gray-500">{{ item.title }}</p>
            </div>
        </div>
    </div>

    <div class="w-full bg-gray-200 items-center justify-center">
        <div style="transform: scale(0.8)" class="w-full"></div>
    </div>
</div>
</template>

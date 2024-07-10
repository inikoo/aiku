<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, onMounted, inject } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { trans } from "laravel-vue-i18n"

import { faPresentation, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { layoutStructure } from '@/Composables/useLayoutStructure'


library.add(faPresentation, faLink)

const props = defineProps<{
    modelValue: Object | String
}>()

console.log(props)

const layout = inject('layout', layoutStructure)
const data = ref([])
const isModalOpen = ref(false)

/* const optionWidthHeight = [
    { label: 'px', value: 'px' },
    { label: '%', value: '%' }
]
 */

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


/* const onEnter = (e) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false })
    emits('autoSave')
    isModalOpen.value = false
} */

const getBannersData = async (): Promise<void> => {
    try {
        const url = route('grp.org.shops.show.web.banners.index', {
            organisation: layout.currentParams.organisation,
            shop: layout.currentParams.shop,
            website: layout.currentParams.website
        });

        const response = await axios.get(url, {
            params: {
                'filter[state]' : 'live'
            }
        });

        data.value = response.data.data;
    } catch (error) {
        console.error(error);
        notify({
            title: "Failed to fetch banners data",
            text: error.message || 'An error occurred',
            type: "error",
        });
    }
};


const onPickBanner = (banner) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false, banner_id : banner })
    emits('autoSave')
    isModalOpen.value = false
}


onMounted(() => {
    getBannersData()
})


</script>

<template>
    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 p-5">
        <li v-for="banner in data.slice(0, 6)" :key="banner.slug"
            class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
            <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 h-28 aspect-w-1 w-full bg-gray-200"
                @click="() => onPickBanner(banner.id)">
                <img v-if="banner['image_thumbnail']" :src="banner['image_thumbnail']"
                    class="w-full object-cover object-center group-hover:opacity-75" />
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <defs>
                        <pattern id="pattern_mQij" patternUnits="userSpaceOnUse" width="13" height="13"
                            patternTransform="rotate(45)">
                            <line x1="0" y="0" x2="0" y2="13" stroke="#CCCCCC" stroke-width="12" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#pattern_mQij)" opacity="0.4" />
                </svg>
            </div>
            <span class="font-bold text-xs">{{ banner.name }}</span>
        </li>
    </ul>
    <div class="flex justify-center">
        <Button label="Load More" type="secondary" @click="isModalOpen = true"></Button>
    </div>



    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <div class="text-center font-semibold text-2xl mb-4">
            {{ trans('Banners') }}
        </div>
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 p-5">
            <li v-for="banner in data" :key="banner.slug"
                class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 h-28 aspect-w-1 w-full bg-gray-200"
                    @click="() => onPickBanner(banner.id)">
                    <img v-if="banner['image_thumbnail']" :src="banner['image_thumbnail']"
                        class="w-full object-cover object-center group-hover:opacity-75" />
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        <defs>
                            <pattern id="pattern_mQij" patternUnits="userSpaceOnUse" width="13" height="13"
                                patternTransform="rotate(45)">
                                <line x1="0" y="0" x2="0" y2="13" stroke="#CCCCCC" stroke-width="12" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#pattern_mQij)" opacity="0.4" />
                    </svg>
                </div>
                <span class="font-bold text-xs">{{ banner.name }}</span>
            </li>
        </ul>
    </Modal>
</template>
